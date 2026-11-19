<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Smartcore\InPostInternational\Api\PickupAddressRepositoryInterface;
use Smartcore\InPostInternational\Model\Config\CountrySettings;
use Smartcore\InPostInternational\Model\InPostShipmentFactory;

class AddressToPointShipmentDtoFactory
{
    /**
     * AddressToPointShipmentDtoFactory constructor
     *
     * @param InPostShipmentFactory $shipmentFactory
     * @param AbstractDtoBuilder $abstractDtoBuilder
     * @param CountrySettings $countrySettings
     * @param Context $context
     * @param Registry $registry
     * @param PickupAddressRepositoryInterface $pickupAddrRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        private readonly InPostShipmentFactory $shipmentFactory,
        private readonly AbstractDtoBuilder       $abstractDtoBuilder,
        private readonly CountrySettings $countrySettings,
        private readonly Context $context,
        private readonly Registry $registry,
        private readonly PickupAddressRepositoryInterface $pickupAddrRepository,
        private readonly ?AbstractResource $resource = null,
        private readonly ?AbstractDb $resourceCollection = null
    ) {
    }

    /**
     * Create a new AddressToPointShipmentDto instance
     *
     * @return AddressToPointShipmentDto
     */
    public function create(): AddressToPointShipmentDto
    {
        return new AddressToPointShipmentDto(
            $this->shipmentFactory,
            $this->abstractDtoBuilder,
            $this->countrySettings,
            $this->context,
            $this->registry,
            $this->pickupAddrRepository,
            $this->resource,
            $this->resourceCollection
        );
    }
}
