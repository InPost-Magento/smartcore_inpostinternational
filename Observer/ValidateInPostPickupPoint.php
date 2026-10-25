<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Smartcore\InPostInternational\Model\ConfigProvider;

class ValidateInPostPickupPoint implements ObserverInterface
{

    /**
     * ValidateInPostPickupPoint constructor.
     *
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
    ) {
    }

    /**
     * Validate that a pickup point is selected for InPost shipping methods
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->configProvider->isEnabledBackendValidation()) {
            return;
        }

        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        if (!$this->configProvider->isPointShippingMethod($shippingMethod)) {
            return;
        }

        if (!$this->hasSelectedPickupPoint($quote)) {
            throw new LocalizedException(
                __('Please select a pickup point for the chosen shipping method.')
            );
        }
    }

    /**
     * Check if pickup is selected
     *
     * @param Quote $quote
     * @return bool
     */
    private function hasSelectedPickupPoint(Quote $quote): bool
    {
        $pointId = $quote->getInpostinternationalLockerId();
        return !empty($pointId) && $pointId !== 'null' && $pointId !== 'undefined';
    }
}
