<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Api\Validators;

use Exception;
use Smartcore\InPostInternational\Exception\AccessTokenValidationException;
use stdClass;

class TokenValidator
{

    /**
     * TokenValidator constructor.
     *
     * @param ClaimsValidator $claimsValidator
     */
    public function __construct(
        private readonly ClaimsValidator $claimsValidator
    ) {
    }

    /**
     * Validate access token
     *
     * @param stdClass $token
     * @return void
     * @throws AccessTokenValidationException
     */
    public function validateAccessToken(stdClass $token): void
    {
        try {
            $this->claimsValidator->validateCommonClaims($token);

            // Only some authorization servers set the typ claim
            if (isset($token->typ) && $token->typ !== 'Bearer') {
                throw new AccessTokenValidationException('Invalid token type');
            }
        } catch (Exception $e) {
            throw new AccessTokenValidationException('Access token validation failed: ' . $e->getMessage());
        }
    }
}
