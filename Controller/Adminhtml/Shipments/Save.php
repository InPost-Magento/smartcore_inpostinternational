<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Controller\Adminhtml\Shipments;

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
        try {
            $formData = $this->getRequest()->getPostValue();
            $this->shipmentProcessor->process($formData);
            $this->messageManager->addSuccessMessage(__('Shipment has been successfully created.')->getText());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } catch (Exception $e) {
            $this->messageManager->addComplexErrorMessage('inpostinternationalApiMessage', [
                'message' => $e->getMessage()
            ]);
            $this->_getSession()->setFormData($formData);
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/create',
                ['order_id' => $formData['shipment_fieldset']['order_id']]
            );
        }
    }
}
