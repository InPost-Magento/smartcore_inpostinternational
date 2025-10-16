<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class CountryShip
{
    /**
     * CountryShip constructor
     *
     * @param CountryCollectionFactory $countryCollFactory
     */
    public function __construct(
        protected CountryCollectionFactory $countryCollFactory,
    ) {
    }

    /**
     * Get options from country settings
     *
     * @param array $allCountries
     * @return array
     */
    public function getOptionsFromCountrySettings(array $allCountries): array
    {
        $countryCollection = $this->countryCollFactory->create()
            ->addFieldToFilter('country_id', ['in' => array_keys($allCountries)])
            ->load();

        $options = [];
        foreach (array_keys($allCountries) as $countryKey) {
            if (!$countryCollection->getItemByColumnValue('country_id', $countryKey)) {
                continue;
            }
            $options[] = [
                'value' => $countryKey,
                'label' => $countryCollection->getItemByColumnValue('country_id', $countryKey)->getName()
            ];
        }
        usort($options, fn ($first, $second) => $first['label'] <=> $second['label']);

        return $options;
    }
}
