<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Smartcore\InPostInternational\Model\ConfigProvider;
use Smartcore\InPostInternational\Service\PickupPointAddressService;

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

    /**
     * UsePickupPointAsShippingAddress constructor.
     *
     * @param ConfigProvider $configProvider
     * @param PickupPointAddressService $pickupPointAddressService
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly PickupPointAddressService $pickupPointAddressService,
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

        if (!$shippingAddress || $pointName === ''
            || !$this->configProvider->isPointShippingMethod($quote->getShippingAddress()->getShippingMethod())
        ) {
            return;
        }

        $pointAddress = $this->pickupPointAddressService->getAddress($quote);

        if ($pointAddress === null) {
            $this->logger->warning(sprintf(
                'InPost pickup point %s_%s has no address, shipping address not replaced',
                $shippingAddress->getCountryId(),
                $pointName
            ));
            return;
        }

        $originalAddress = array_intersect_key($shippingAddress->getData(), array_flip(self::ORIGINAL_ADDRESS_FIELDS));
        $order->setData('inpostinternational_original_shipping_address', $this->json->serialize($originalAddress));

        // Update the existing address: Order::setShippingAddress() doesn't save a replacement address object.
        $shippingAddress->addData($pointAddress + [
            'region_id' => null,
            'customer_address_id' => null,
        ]);
    }
}
