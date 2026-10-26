<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AuthMethod implements OptionSourceInterface
{
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const CLIENT_CREDENTIALS = 'client_credentials';

    /**
     * Get authentication methods
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            ['value' => self::AUTHORIZATION_CODE, 'label' => __('Login with InPost')->render()],
            ['value' => self::CLIENT_CREDENTIALS, 'label' => __('Client credentials')->render()],
        ];
    }
}
