<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;
use Smartcore\InPostInternational\Model\Config\Source\ShippingMethods;
use Smartcore\InPostInternational\Model\ConfigProvider;
use Smartcore\InPostInternational\Model\InPostShipment;
use Smartcore\InPostInternational\Model\InPostShipmentRepository;

class InPostInternationalOrderView extends AbstractOrder
{

    /**
     * InpostOrderView constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param ShippingMethods $shippingMethods
     * @param InPostShipmentRepository $shipmentRepository
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context                                   $context,
        Registry                                  $registry,
        Admin                                     $adminHelper,
        private readonly ShippingMethods          $shippingMethods,
        private readonly InPostShipmentRepository $shipmentRepository,
        private readonly ConfigProvider           $configProvider,
        array                                     $data = [],
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Get the list of Inpost shipping types
     *
     * @param string|null $shippingMethod
     * @return array<mixed>
     */
    public function getInpostShippingTypes(?string $shippingMethod): array
    {
        $destinationType = $this->shippingMethods->getShippingMethodDestinationType($shippingMethod);
        return [
            [
                'value' => 'address',
                'label' => __('InPost International to address'),
                'selected' => $destinationType === 'address',
            ],
            [
                'value' => 'point',
                'label' => __('InPost International to point (parcel locker)'),
                'selected' => $destinationType === 'point',
            ],
        ];
    }

    /**
     * Get the Inpost shipments associated with the order
     *
     * @return array<InPostShipment>
     */
    public function getInpostShipments(): array
    {
        try {
            $order = $this->getOrder();
            return $this->shipmentRepository->getListByOrderId((string) $order->getId());
        } catch (LocalizedException $exception) {
            return [];
        }
    }

    /**
     * Get the tracking URL for the shipment
     *
     * @param InPostShipment $shipment
     * @return string
     */
    public function getShippingTrackingUrl(InPostShipment $shipment): string
    {
        $tracking = $shipment->getTrackingNumber();
        return $this->configProvider->getInPostTrackingLink() . $tracking;
    }

    /**
     * Get the label URL for the shipment
     *
     * @param InPostShipment $shipment
     * @return string
     */
    public function getLabelUrl(InPostShipment $shipment): string
    {
        return $this->getUrl(
            'inpostinternational/shipment/label',
            ['id' => $shipment->getId()]
        );
    }

    /**
     * Get the Inpost locker ID for the order
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getSelectedLockerId(): mixed
    {
        return $this->getOrder()->getInpostinternationalLockerId();
    }
}
