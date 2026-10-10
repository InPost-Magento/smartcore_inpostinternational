<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Carrier;

use Smartcore\InPostInternational\Setup\Patch\Data\AddProductBlockPointsAttribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InpostCourier extends AbstractInternationalCourier
{

    /**
     * @var string
     */
    protected $_code = 'inpostinternationalcourier';

    /**
     * @var string
     */
    protected string $_method = 'courier';

    /**
     * @var array<string>
     */
    protected array $countryAllowed = ['IT', 'ES', 'PT', 'AT', 'CZ', 'SK'];

    /**
     * @var string|null
     */
    protected ?string $blockAttribute = AddProductBlockPointsAttribute::ATTRIBUTE_CODE;

    /**
     * @var string
     */
    public string $destinationType = 'point';
}
