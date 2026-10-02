<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Carrier;

use Smartcore\InPostInternational\Setup\Patch\Data\AddProductBlockAddressAttribute;

/**
 * InPost International Courier To Door carrier model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InPostInternationalD2D extends AbstractInternationalCourier
{

    /**
     * @var string
     */
    protected $_code = 'inpostinternationald2d';

    /**
     * @var string
     */
    protected string $_method = 'address';

    /**
     * @var array<string>
     */
    protected array $countryAllowed = ['AT', 'HU', 'CZ', 'SK'];

    /**
     * @var string|null
     */
    protected ?string $blockAttribute = AddProductBlockAddressAttribute::ATTRIBUTE_CODE;

    /**
     * @var string
     */
    public string $destinationType = 'address';
}
