<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Psr\Log\LoggerInterface;
use Smartcore\InPostInternational\Model\ConfigProvider;
use Smartcore\InPostInternational\Service\PickupPointService;

class UsePickupPointAsShippingAddress implements ObserverInterface
{
    private const ORIGINAL_ADDRESS_FIELDS = [
        'firstname',
        'lastname',
        'company',
        'street',
        'city',
        'region',
        'region_id',
        'postcode',
        'country_id',
        'telephone',
        'customer_address_id',
    ];

    private const NUMBER_FIRST_COUNTRIES = ['GB', 'IE', 'FR', 'LU'];

    /**
     * UsePickupPointAsShippingAddress constructor.
     *
     * @param ConfigProvider $configProvider
     * @param PickupPointService $pickupPointService
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly PickupPointService $pickupPointService,
        private readonly Json $json,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Replace the order shipping address with the selected pickup point
     *
     * The customer's name and contact details are kept and the original address is stored on the order.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->configProvider->isPointAsShippingAddressEnabled()) {
            return;
        }

        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $pointName = (string) $quote->getInpostinternationalLockerId();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        if (!$shippingAddress || $pointName === '' || !$this->configProvider->isPointShippingMethod($shippingMethod)) {
            return;
        }

        $countryCode = (string) $shippingAddress->getCountryId();
        $pointAddress = $this->getPointAddress($quote, $countryCode, $pointName);

        if ($pointAddress === null) {
            $this->logger->warning(sprintf(
                'InPost pickup point %s_%s has no address, shipping address not replaced',
                $countryCode,
                $pointName
            ));
            return;
        }

        $originalAddress = array_intersect_key($shippingAddress->getData(), array_flip(self::ORIGINAL_ADDRESS_FIELDS));
        $order->setData('inpostinternational_original_shipping_address', $this->json->serialize($originalAddress));

        // Update the existing address: Order::setShippingAddress() doesn't save a replacement address object.
        $shippingAddress->addData($pointAddress + [
            'company' => (string) __('InPost %1', $pointName),
            'region_id' => null,
            'customer_address_id' => null,
        ]);
    }

    /**
     * Get pickup point address, preferring the InPost Points API over the geowidget data stored on the quote
     *
     * @param Quote $quote
     * @param string $countryCode
     * @param string $pointName
     * @return array|null
     */
    private function getPointAddress(Quote $quote, string $countryCode, string $pointName): ?array
    {
        if ($this->configProvider->isEnabledBackendApiValidation()) {
            try {
                $point = $this->pickupPointService->getPoint($countryCode, $pointName);
                $address = $point['address'] ?? [];

                if ($address) {
                    return $this->buildAddress(
                        $this->formatStreet($address['street'] ?? '', $address['buildingNumber'] ?? '', $countryCode),
                        $address['city'] ?? '',
                        $address['postalCode'] ?? '',
                        $address['administrativeArea'] ?? '',
                        $address['country'] ?? $countryCode
                    );
                }
            } catch (Exception $e) {
                $this->logger->warning(
                    sprintf('InPost pickup point %s_%s lookup failed: %s', $countryCode, $pointName, $e->getMessage())
                );
            }
        }

        try {
            $lockerData = $this->json->unserialize((string) $quote->getInpostinternationalLockerData());
        } catch (Exception $e) {
            return null;
        }

        $address = is_array($lockerData) ? ($lockerData['address_details'] ?? []) : [];

        return $this->buildAddress(
            $this->formatStreet($address['street'] ?? '', $address['building_number'] ?? '', $countryCode),
            $address['city'] ?? '',
            $address['post_code'] ?? '',
            $address['province'] ?? '',
            $countryCode
        );
    }

    /**
     * Combine street and building number in the order used by the destination country
     *
     * @param string $street
     * @param string $buildingNumber
     * @param string $countryCode
     * @return string
     */
    private function formatStreet(string $street, string $buildingNumber, string $countryCode): string
    {
        $parts = in_array($countryCode, self::NUMBER_FIRST_COUNTRIES, true)
            ? [$buildingNumber, $street]
            : [$street, $buildingNumber];

        return trim(implode(' ', $parts));
    }

    /**
     * Build address data, or null if any required part is missing
     *
     * @param string $street
     * @param string $city
     * @param string $postcode
     * @param string $region
     * @param string $countryCode
     * @return array|null
     */
    private function buildAddress(
        string $street,
        string $city,
        string $postcode,
        string $region,
        string $countryCode
    ): ?array {
        if ($street === '' || $city === '' || $postcode === '') {
            return null;
        }

        return [
            'street' => $street,
            'city' => $city,
            'postcode' => $postcode,
            'region' => $region,
            'country_id' => $countryCode,
        ];
    }
}
