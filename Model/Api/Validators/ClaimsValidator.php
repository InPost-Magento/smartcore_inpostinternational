<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Api\Validators;

use Smartcore\InPostInternational\Exception\InvalidAuthorizedPartyException;
use Smartcore\InPostInternational\Exception\InvalidIssuerException;
use Smartcore\InPostInternational\Exception\MissingRequiredClaimsException;
use Smartcore\InPostInternational\Model\Api\WellKnownService;
use Smartcore\InPostInternational\Model\ConfigProvider;
use stdClass;

class ClaimsValidator
{
    /**
     * ClaimsValidator constructor.
     *
     * @param ConfigProvider $configProvider
     * @param WellKnownService $wellKnownService
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly WellKnownService $wellKnownService
    ) {
    }

    /**
     * Validate common claims
     *
     * @param stdClass $decoded
     * @return void
     * @throws InvalidIssuerException
     * @throws InvalidAuthorizedPartyException
     * @throws MissingRequiredClaimsException
     */
    public function validateCommonClaims(stdClass $decoded): void
    {
        if ($decoded->iss !== $this->wellKnownService->getIssuer()) {
            throw new InvalidIssuerException('Invalid issuer');
        }

        // Not every authorization server sets azp; client credentials tokens identify the client by sub
        $authorizedParty = $decoded->azp ?? $decoded->client_id ?? $decoded->sub ?? null;

        if ($authorizedParty !== $this->configProvider->getClientId()) {
            throw new InvalidAuthorizedPartyException('Invalid authorized party');
        }

        if (!isset($decoded->sub) || !isset($decoded->jti) || !isset($decoded->scope) || !isset($decoded->exp)) {
            throw new MissingRequiredClaimsException('Missing required claims');
        }
    }
}
