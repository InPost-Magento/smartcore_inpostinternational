<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Smartcore\InPostInternational\Api\PickupAddressRepositoryInterface;
use Smartcore\InPostInternational\Model\InPostShipmentFactory;

class AddressToAddressShipmentDtoFactory
{
    /**
     * @param InPostShipmentFactory $shipmentFactory
     * @param AbstractDtoBuilder $abstractDtoBuilder
     * @param Context $context
     * @param Registry $registry
     * @param PickupAddressRepositoryInterface $pickupAddrRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        private readonly InPostShipmentFactory $shipmentFactory,
        private readonly AbstractDtoBuilder $abstractDtoBuilder,
        private readonly Context $context,
        private readonly Registry $registry,
        private readonly PickupAddressRepositoryInterface $pickupAddrRepository,
        private readonly ?AbstractResource $resource = null,
        private readonly ?AbstractDb $resourceCollection = null
    ) {
    }

    /**
     * Create AddressToAddressShipmentDto instance
     *
     * @return AddressToAddressShipmentDto
     */
    public function create(): AddressToAddressShipmentDto
    {
        return new AddressToAddressShipmentDto(
            $this->shipmentFactory,
            $this->abstractDtoBuilder,
            $this->context,
            $this->registry,
            $this->pickupAddrRepository,
            $this->resource,
            $this->resourceCollection
        );
    }
}
