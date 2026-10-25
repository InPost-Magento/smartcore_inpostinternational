<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Smartcore\InPostInternational\Model\Config\CountrySettings;

class OriginCountry extends CountryShip implements OptionSourceInterface
{
    /**
     * OriginCountry constructor.
     *
     * @param CountryCollectionFactory $countryCollFactory
     * @param CountrySettings $countrySettings
     */
    public function __construct(
        protected CountryCollectionFactory $countryCollFactory,
        protected CountrySettings          $countrySettings
    ) {
        parent::__construct($countryCollFactory);
    }

    /**
     * Get countries InPost can collect from
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->getOptionsFromCountrySettings($this->countrySettings->getCountryCanShipFromSettings());
    }
}
