<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Api;

use Exception;
use Magento\Framework\HTTP\Client\CurlFactory;
use Smartcore\InPostInternational\Exception\AccessTokenValidationException;
use Smartcore\InPostInternational\Exception\TokenSaveException;
use Smartcore\InPostInternational\Model\Api\Validators\TokenValidator;
use Smartcore\InPostInternational\Model\ConfigProvider;
use stdClass;

class AccessTokenService
{

    public const ACCESS_TOKEN_EXPIRING_AT_THRESHOLD = 30;

    /**
     * AccessTokenService constructor.
     *
     * @param ConfigProvider $configProvider
     * @param WellKnownService $wellKnownService
     * @param JwksService $jwksService
     * @param JwtService $jwtService
     * @param CurlFactory $curlFactory
     * @param TokenValidator $tokenValidator
     */
    public function __construct(
        private readonly ConfigProvider   $configProvider,
        private readonly WellKnownService $wellKnownService,
        private readonly JwksService      $jwksService,
        private readonly JwtService       $jwtService,
        private readonly CurlFactory      $curlFactory,
        private readonly TokenValidator   $tokenValidator,
    ) {
    }

    /**
     * Get access token - main method
     *
     * @return string
     * @throws TokenSaveException
     */
    public function getAccessToken(): string
    {
        $accessToken = $this->configProvider->getRawAccessToken();
        $accessTokenExpiresAt = $this->configProvider->getAccessTokenExpiresAt();

        if (time() + self::ACCESS_TOKEN_EXPIRING_AT_THRESHOLD > $accessTokenExpiresAt) {
            try {
                $accessToken = $this->refreshAccessToken();
                $this->saveAccessToken($accessToken);
            } catch (Exception $e) {
                throw new TokenSaveException(sprintf(
                    'Failed to save access token after refresh: %s',
                    $e->getMessage()
                ));
            }

        }

        return $accessToken;
    }

    /**
     * Save access token
     *
     * @param string $accessToken
     * @throws AccessTokenValidationException
     */
    public function saveAccessToken(string $accessToken): void
    {
        $decodedAccessToken = $this->getDecodedAccessToken($accessToken);
        $this->tokenValidator->validateAccessToken($decodedAccessToken);
        $this->configProvider->saveAccessTokenExpiresAt($decodedAccessToken->exp);
        $this->configProvider->saveAccessToken($accessToken);
    }

    /**
     * Decode and validate access token
     *
     * @param string $token
     * @return stdClass
     */
    public function getDecodedAccessToken(string $token): stdClass
    {
        $jwks = $this->getJwks();
        $keySet = $this->jwtService->parseKeySet($jwks);
        return $this->jwtService->decode($token, $keySet);
    }

    /**
     * Get JSON Web Key Set
     *
     * @return array
     */
    private function getJwks(): array
    {
        $jwksUri = $this->wellKnownService->getJwksUri();

        return $this->jwksService->getJwks($jwksUri);
    }

    /**
     * Request for access token refresh
     *
     * @return string
     * @throws TokenSaveException
     */
    private function refreshAccessToken(): string
    {
        $params = [
            'client_id' => $this->configProvider->getClientId(),
            'client_secret' => $this->configProvider->getClientSecret(),
        ];

        // Client credentials gets a new token directly, there's no refresh token from logging in to InPost
        if ($this->configProvider->isClientCredentialsAuth()) {
            $params['grant_type'] = 'client_credentials';
            $params['scope'] = $this->configProvider->getClientCredentialsScope();
        } else {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $this->configProvider->getRawRefreshToken();
        }

        $curl = $this->curlFactory->create();
        $curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $curl->post($this->wellKnownService->getTokenEndpoint(), http_build_query($params));
        $result = json_decode($curl->getBody());

        if (property_exists($result, 'error')) {
            throw new TokenSaveException(
                sprintf(
                    'Error during access token refresh: "%s". Full response: "%s"',
                    $result->error_description ?? '',
                    $curl->getBody()
                )
            );
        }

        if ($result === null || !property_exists($result, 'access_token')) {
            throw new TokenSaveException(
                sprintf(
                    'Failed to refresh access token. Response is empty or does not contain access_token. "%s"',
                    $curl->getBody()
                )
            );
        }

        return $result?->access_token ?? '';
    }
}
