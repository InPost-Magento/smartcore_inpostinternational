<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Smartcore\InPostInternational\Api\Data\ShipmentInterface;
use Smartcore\InPostInternational\Api\ShipmentRepositoryInterface;
use Smartcore\InPostInternational\Model\ResourceModel\Shipment as ResourceModel;
use Smartcore\InPostInternational\Model\ResourceModel\Shipment\CollectionFactory;

class ShipmentRepository implements ShipmentRepositoryInterface
{

    /**
     * ShipmentRepository constructor.
     *
     * @param ResourceModel $resourceModel
     * @param ShipmentFactory $shipmentFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly ResourceModel     $resourceModel,
        private readonly ShipmentFactory   $shipmentFactory,
        private readonly CollectionFactory $collectionFactory,
    ) {
    }

    /**
     * Shipment save
     *
     * @param ShipmentInterface&AbstractModel $shipment
     * @return ShipmentInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function save(ShipmentInterface $shipment): ShipmentInterface
    {
        $this->resourceModel->save($shipment);
        return $shipment;
    }

    /**
     * Shipment delete
     *
     * @param ShipmentInterface&AbstractModel $shipment
     * @return $this
     * @throws Exception
     */
    public function delete(ShipmentInterface $shipment): static
    {
        $this->resourceModel->delete($shipment);
        return $this;
    }

    /**
     * Load Shipment
     *
     * @param int $modelId
     * @return ShipmentInterface
     */
    public function load(int $modelId): ShipmentInterface
    {
        $shipment = $this->shipmentFactory->create();
        $this->resourceModel->load($shipment, $modelId);
        return $shipment;
    }

    /**
     * Get list of shipments
     *
     * @param string|null $updatedSince
     * @return array<mixed>
     */
    public function getList(?string $updatedSince = null): array
    {
        $collection = $this->collectionFactory->create();

        if ($updatedSince) {
            $collection->addFieldToFilter('updated_at', ['gteq' => $updatedSince]);
        }

        return $collection->getItems();
    }

    /**
     * Get list of shipments by order ID
     *
     * @param string $orderId
     * @return array<mixed>
     */
    public function getListByOrderId(string $orderId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', $orderId);

        return $collection->getItems();
    }
}
