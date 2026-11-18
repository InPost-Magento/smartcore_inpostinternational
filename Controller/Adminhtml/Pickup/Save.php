<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Controller\Adminhtml\Pickup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Smartcore\InPostInternational\Service\OneTimePickupProcessor;

class Save extends Action
{

    /**
     * Save constructor
     *
     * @param Context $context
     * @param OneTimePickupProcessor $otpProcessor
     */
    public function __construct(
        Context                          $context,
        private readonly OneTimePickupProcessor $otpProcessor,
    ) {
        parent::__construct($context);
    }

    /**
     * Save Pickup
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $formData = [];
        try {
            $formData = $this->getRequest()->getPostValue();
            $this->otpProcessor->process($formData);
            $this->messageManager->addSuccessMessage(__('Pickup has been saved.')->render());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addComplexErrorMessage('inpostinternationalApiMessage', [
                'message' => $e->getMessage()
            ]);
            $this->_getSession()->setFormData($formData);
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/create',
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
        return $this->_authorization->isAllowed('Smartcore_InPostInternational::pickup_create');
    }
}
