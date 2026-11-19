<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Controller\Adminhtml\Shipment;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Smartcore\InPostInternational\Service\ShipmentProcessor;

class Save extends Action
{

    /**
     * Shipments Save constructor.
     *
     * @param Context $context
     * @param ShipmentProcessor $shipmentProcessor
     */
    public function __construct(
        Context $context,
        private readonly ShipmentProcessor $shipmentProcessor
    ) {
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $formData = [];
        try {
            $formData = $this->getRequest()->getPostValue();
            $this->shipmentProcessor->process($formData);
            $this->messageManager->addSuccessMessage(__('Shipment has been successfully created.')->render());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } catch (Exception $e) {
            $this->messageManager->addComplexErrorMessage('inpostinternationalApiMessage', [
                'message' => $e->getMessage()
            ]);
            $this->_getSession()->setFormData($formData);
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/create',
                ['order_id' => $formData[ShipmentProcessor::SHIPMENT_FIELDSET]['order_id'] ?? null]
            );
        }
    }

    /**
     * Check if user has permissions to visit the controller
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Smartcore_InPostInternational::shipment_create');
    }
}
