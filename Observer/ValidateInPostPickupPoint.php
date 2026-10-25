<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Smartcore\InPostInternational\Model\ConfigProvider;
use Smartcore\InPostInternational\Service\PickupPointService;

class ValidateInPostPickupPoint implements ObserverInterface
{

    /**
     * ValidateInPostPickupPoint constructor.
     *
     * @param ConfigProvider $configProvider
     * @param PickupPointService $pickupPointService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly PickupPointService $pickupPointService,
        private readonly LoggerInterface $logger,
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

        if ($this->configProvider->isEnabledBackendApiValidation()) {
            $this->validatePickupPointWithApi($quote);
        }
    }

    /**
     * Check the selected pickup point exists using the InPost Points API
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    private function validatePickupPointWithApi(Quote $quote): void
    {
        $pointName = (string) $quote->getInpostinternationalLockerId();
        $countryCode = (string) $quote->getShippingAddress()->getCountryId();

        try {
            $point = $this->pickupPointService->getPoint($countryCode, $pointName);
        } catch (Exception $e) {
            // Don't lose the order if InPost can't be reached; the point was still chosen from the geowidget.
            $this->logger->warning(
                sprintf(
                    'InPost pickup point %s_%s could not be validated: %s',
                    $countryCode,
                    $pointName,
                    $e->getMessage()
                )
            );
            return;
        }

        if ($point === null) {
            throw new LocalizedException(
                __('The selected pickup point is not available. Please choose another pickup point.')
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
