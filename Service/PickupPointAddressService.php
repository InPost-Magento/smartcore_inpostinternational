<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Service;

use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Smartcore\InPostInternational\Model\ConfigProvider;

class PickupPointAddressService
{
    private const NUMBER_FIRST_COUNTRIES = ['GB', 'IE', 'FR', 'LU'];

    /**
     * PickupPointAddressService constructor.
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
     * Get the address of the quote's selected pickup point, for its point delivery method
     *
     * Returns street, city, postcode, region, country and company (InPost <point>), or null if the quote has no
     * pickup point or its address is incomplete.
     *
     * @param Quote $quote
     * @param bool $useApi Look the point up with the Points API, when API validation is enabled
     * @return array|null
     */
    public function getAddress(Quote $quote, bool $useApi = true): ?array
    {
        $shippingAddress = $quote->getShippingAddress();
        $pointName = (string) $quote->getInpostinternationalLockerId();

        if ($pointName === ''
            || !$this->configProvider->isPointShippingMethod((string) $shippingAddress->getShippingMethod())
        ) {
            return null;
        }

        $countryCode = (string) $shippingAddress->getCountryId();
        $address = $this->getPointAddress($quote, $countryCode, $pointName, $useApi);

        return $address === null ? null : $address + ['company' => (string) __('InPost %1', $pointName)];
    }

    /**
     * Get pickup point address, preferring the InPost Points API over the geowidget data stored on the quote
     *
     * @param Quote $quote
     * @param string $countryCode
     * @param string $pointName
     * @param bool $useApi
     * @return array|null
     */
    private function getPointAddress(Quote $quote, string $countryCode, string $pointName, bool $useApi): ?array
    {
        if ($useApi && $this->configProvider->isEnabledBackendApiValidation()) {
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
