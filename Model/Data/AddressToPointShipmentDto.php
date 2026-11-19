<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Smartcore\InPostInternational\Api\PickupAddressRepositoryInterface;
use Smartcore\InPostInternational\Model\Config\CountrySettings;
use Smartcore\InPostInternational\Model\Data\Trait\InsuranceCreatorTrait;
use Smartcore\InPostInternational\Model\Data\Trait\OriginAddressCreatorTrait;
use Smartcore\InPostInternational\Model\InPostShipment as ShipmentModel;
use Smartcore\InPostInternational\Model\InPostShipmentFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressToPointShipmentDto extends ShipmentTypeDto implements ShipmentTypeInterface
{
    use InsuranceCreatorTrait;
    use OriginAddressCreatorTrait;
    public const ADDRESS_TO_POINT = 'address-to-point';
    public const LABEL = 'From address to point';

    /**
     * AddressToPointShipmentDto constructor.
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
        readonly InPostShipmentFactory $shipmentFactory,
        private readonly AbstractDtoBuilder       $abstractDtoBuilder,
        private readonly CountrySettings $countrySettings,
        Context                  $context,
        Registry                 $registry,
        private readonly PickupAddressRepositoryInterface $pickupAddrRepository,
        ?AbstractResource        $resource = null,
        ?AbstractDb              $resourceCollection = null
    ) {
        parent::__construct($shipmentFactory, $context, $registry, $resource, $resourceCollection);
    }

    /**
     * Get API endpoint for shipment type
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return self::ADDRESS_TO_POINT;
    }

    /**
     * Get the label for the shipment type
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::LABEL;
    }

    /**
     * Convert the DTO to a database model
     *
     * @return ShipmentModel
     */
    public function toDbModel(): ShipmentModel
    {
        $shipmentDbModel = parent::toDbModel();
        $shipment = $this->getShipment();
        $originAddress = $shipment->getOrigin()->getAddress();

        $shipmentDbModel->setOriginHouseNumber($originAddress->getHouseNumber())
            ->setOriginFlatNumber($originAddress->getFlatNumber())
            ->setOriginStreet($originAddress->getStreet())
            ->setOriginCity($originAddress->getCity())
            ->setOriginPostalCode($originAddress->getPostalCode())
            ->setOriginCountryCode($originAddress->getCountryCode());

        return $shipmentDbModel;
    }

    /**
     * Create origin object
     *
     * @param array<string,mixed> $shipmentFieldsetData
     * @return OriginDto
     */
    public function createOrigin(array $shipmentFieldsetData): OriginDto
    {
        return $this->createOriginAddress($shipmentFieldsetData);
    }

    /**
     * Get the label format for the shipment
     *
     * @return string
     */
    public function getLabelFormat(): string
    {
        return $this->getData(self::LABEL_FORMAT);
    }

    /**
     * Set the label format for the shipment
     *
     * @param string $labelFormat
     * @return $this
     */
    public function setLabelFormat(string $labelFormat): self
    {
        $this->setData(self::LABEL_FORMAT, $labelFormat);
        return $this;
    }

    /**
     * Get the shipment details for address-to-point delivery
     *
     * @return ShipmentDto
     */
    public function getShipment(): ShipmentDto
    {
        return $this->getData(self::SHIPMENT);
    }

    /**
     * Set the shipment details for address-to-point delivery
     *
     * @param ShipmentDto $shipment
     * @return $this
     */
    public function setShipment(ShipmentDto $shipment): self
    {
        $this->setData(self::SHIPMENT, $shipment);
        return $this;
    }

    /**
     * Create destination object
     *
     * @param array<string,mixed> $shipmentFieldsetData
     * @return DestinationInterface
     */
    public function createDestination(array $shipmentFieldsetData): DestinationInterface
    {
        /** @var DestinationInterface $destination */
        $destination = $this->abstractDtoBuilder->buildDtoInstance(DestinationDto::class);
        $destination
            ->setCountryCode($shipmentFieldsetData['destination_country_' . self::ADDRESS_TO_POINT])
            ->setPointName($shipmentFieldsetData['point_name']);

        return $destination;
    }

    /**
     * Create value added services object
     *
     * @param array<string,mixed> $shipmentFieldsetData
     * @return ValueAddedServicesDto|null
     */
    public function createValueAddedServices(array $shipmentFieldsetData): ?ValueAddedServicesDto
    {
        if ($this->countrySettings->canCountryUseInsurance(
            $shipmentFieldsetData['destination_country_' . self::ADDRESS_TO_POINT]
        )
        ) {
            return $this->createValueAddedServicesWithInsurance($shipmentFieldsetData);
        }
        return null;
    }
}
