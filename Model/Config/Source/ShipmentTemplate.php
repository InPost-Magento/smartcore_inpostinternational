<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config;
use Smartcore\InPostInternational\Api\ParcelTemplateRepositoryInterface;

class ShipmentTemplate implements OptionSourceInterface
{
    /**
     * Shipping methods mapper
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     * @param ParcelTemplateRepositoryInterface $parcelTmplRepository
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Config $shippingConfig,
        protected ParcelTemplateRepositoryInterface $parcelTmplRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $methods = [];
        $templates = $this->parcelTmplRepository->getList();
        $defaultSet = false;

        foreach ($templates as $template) {
            $sizeUnit = $template->getDimensionUnit();
            $option = [
                'value' => $template->getId(),
                'label' => sprintf(
                    '%s (%d%s x %d%s x %d%s, %d%s)',
                    $template->getLabel(),
                    $template->getLength(),
                    $sizeUnit,
                    $template->getWidth(),
                    $sizeUnit,
                    $template->getHeight(),
                    $sizeUnit,
                    $template->getWeight(),
                    $template->getWeightUnit()
                ),
            ];

            if (!$defaultSet && $template->isDefault()) {
                $option['selected'] = 'selected';
                $defaultSet = true;
            }

            $methods[] = $option;
        }

        return $methods;
    }
}
