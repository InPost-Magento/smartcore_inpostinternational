<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config;

use Magento\Framework\Locale\TranslatedLists;

class CountrySettings
{
    /**
     * @var array|array[]
     */
    private array $countrySettings = [
        'IT' => [
            'languageCode' => 'it_IT',
            'phonePrefix' => '+39',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'FR' => [
            'languageCode' => 'fr_FR',
            'phonePrefix' => '+33',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => true,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'LU' => [
            'languageCode' => 'fr_LU',
            'phonePrefix' => '+352',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => true,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'BE' => [
            'languageCode' => 'nl_BE',
            'phonePrefix' => '+32',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => true,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'NL' => [
            'languageCode' => 'nl_NL',
            'phonePrefix' => '+31',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => true,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'ES' => [
            'languageCode' => 'es_ES',
            'phonePrefix' => '+34',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'PT' => [
            'languageCode' => 'pt_PT',
            'phonePrefix' => '+351',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'PL' => [
            'languageCode' => 'pl_PL',
            'phonePrefix' => '+48',
            'currency' => 'PLN',
            'canInPostShipPointToPoint' => false,
            'canInPostShipAddressToPoint' => false,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => true,
            'canInsurance' => true
        ],
        'GB' => [
            'languageCode' => 'en_GB',
            'phonePrefix' => '+44',
            'currency' => 'GBP',
            'canInPostShipPointToPoint' => false,
            'canInPostShipAddressToPoint' => false,
            'canInPostShipPointToAddress' => false,
            'canInPostShipAddressToAddress' => false,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'AT' => [
            'languageCode' => 'de_AT',
            'phonePrefix' => '+43',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => true,
            'canInPostShipAddressToAddress' => true,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => false
        ],
        'CZ' => [
            'languageCode' => 'cs_CZ',
            'phonePrefix' => '+420',
            'currency' => 'CZK',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => true,
            'canInPostShipAddressToAddress' => true,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'HU' => [
            'languageCode' => 'hu_HU',
            'phonePrefix' => '+36',
            'currency' => 'HUF',
            'canInPostShipPointToPoint' => false,
            'canInPostShipAddressToPoint' => false,
            'canInPostShipPointToAddress' => true,
            'canInPostShipAddressToAddress' => true,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
        'SK' => [
            'languageCode' => 'sk_SK',
            'phonePrefix' => '+421',
            'currency' => 'EUR',
            'canInPostShipPointToPoint' => true,
            'canInPostShipAddressToPoint' => true,
            'canInPostShipPointToAddress' => true,
            'canInPostShipAddressToAddress' => true,
            'canMondialRelayShipTo' => false,
            'canShipFrom' => false,
            'canInsurance' => true
        ],
    ];

    /**
     * CountrySettings constructor.
     *
     * @param TranslatedLists $translatedLists
     */
    public function __construct(
        private readonly TranslatedLists $translatedLists
    ) {
    }

    /**
     * Get all country settings
     *
     * @return array|array[]|null
     */
    public function getAllCountrySettings(): ?array
    {
        return $this->countrySettings ?? null;
    }

    /**
     * Get countries that can be shipped to points by InPost
     *
     * @return array|array[]
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getCountryCanInPostShipToPointSettings(): array
    {
        $pointToPointCountries = $this->getCountryCanInPostShipPointToPointSettings();
        $addressToPointCountries = $this->getCountryCanInPostShipAddressToPointSettings();
        return array_merge($pointToPointCountries, $addressToPointCountries);
    }

    /**
     * Get countries that can be shipped point to point by InPost
     *
     * @return array|array[]
     */
    public function getCountryCanInPostShipPointToPointSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canInPostShipPointToPoint']);
    }

    /**
     * Get countries that can be shipped address to point by InPost
     *
     * @return array|array[]
     */
    public function getCountryCanInPostShipAddressToPointSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canInPostShipAddressToPoint']);
    }

    /**
     * Get countries that can be shipped to address by InPost
     *
     * @return array|array[]
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getCountryCanInPostShipToAddressSettings(): array
    {
        $pointToAddressCountries = $this->getCountryCanInPostShipPointToAddressSettings();
        $addressToAddressCountries = $this->getCountryCanInPostShipAddressToAddressSettings();
        return array_merge($pointToAddressCountries, $addressToAddressCountries);
    }

    /**
     * Get countries that can be shipped point to address by InPost
     *
     * @return array|array[]
     */
    public function getCountryCanInPostShipPointToAddressSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canInPostShipPointToAddress']);
    }

    /**
     * Get countries that can be shipped address to address by InPost
     *
     * @return array|array[]
     */
    public function getCountryCanInPostShipAddressToAddressSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canInPostShipAddressToAddress']);
    }

    /**
     * Get countries that can be shipped to by Mondial Relay
     *
     * @return array|array[]
     */
    public function getCountryCanMondialRelayShipToSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canMondialRelayShipTo']);
    }

    /**
     * Get countries that can be shipped from
     *
     * @return array|array[]
     */
    public function getCountryCanShipFromSettings(): array
    {
        return array_filter($this->countrySettings, fn ($country) => $country['canShipFrom']);
    }

    /**
     * Get language code by country code
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getLanguageCode(string $countryCode): ?string
    {
        return $this->countrySettings[$countryCode]['languageCode'] ?? null;
    }

    /**
     * Get phone prefix by country code
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getPhonePrefix(string $countryCode): ?string
    {
        return $this->countrySettings[$countryCode]['phonePrefix'] ?? null;
    }

    /**
     * Get phone number without prefix for country
     *
     * @param string $phoneNumber
     * @param string $countryCode
     * @return array|string|null
     */
    public function getPhoneNumberWithoutPrefixForCountry(string $phoneNumber, string $countryCode): array|string|null
    {
        $prefix = $this->countrySettings[$countryCode]['phonePrefix'] ?? null;
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if ($prefix && str_starts_with($phoneNumber, $prefix)) {
            return substr($phoneNumber, strlen($prefix));
        }

        return $phoneNumber;
    }

    /**
     * Get currency by country code
     *
     * @param string $countryCode
     * @return mixed
     */
    public function getCurrency(string $countryCode): mixed
    {
        return $this->countrySettings[$countryCode]['currency'] ?? null;
    }

    /**
     * Get language name
     *
     * @param string $languageCode
     * @return string|null
     */
    public function getLanguageName(string $languageCode): ?string
    {
        $locales = $this->translatedLists->getOptionLocales();
        foreach ($locales as $locale) {
            if ($locale['value'] === $languageCode) {
                return $locale['label'];
            }
        }
        return null;
    }

    /**
     * Check if country can use insurance
     *
     * @param string $countryCode
     * @return bool
     */
    public function canCountryUseInsurance(string $countryCode): bool
    {
        return $this->countrySettings[$countryCode]['canInsurance'] ?? false;
    }
}
