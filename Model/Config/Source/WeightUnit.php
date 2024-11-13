<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WeightUnit implements OptionSourceInterface
{
    private const string KILOGRAM = 'kg';
    private const string GRAM = 'g';

    /**
     * @inheritdoc
     */
    public function toOptionArray() : array
    {
        return [
            ['value' => self::KILOGRAM, 'label' => __('Kilograms')],
            ['value' => self::GRAM, 'label' => __('Grams')],
        ];
    }
}
