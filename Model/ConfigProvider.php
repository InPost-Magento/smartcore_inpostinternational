<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model;

use Magento\Backend\Model\UrlInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\FlagManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public const string SHIPPING_CONFIG_PATH = 'shipping/inpostinternational/';
    public const string CARRIERS_CONFIG_PATH = 'carriers/inpostinternationalcourier/';
    private const string ACCESS_TOKEN_EXPIRES_AT = 'access_token_expires_at';
    private const string ACCESS_TOKEN = 'access_token';
    private const string REFRESH_TOKEN = 'refresh_token';
    private const string CODE_VERIFIER = 'code_verifier';

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $adminUrl
     * @param EncryptorInterface $encryptor
     * @param Config $_resourceConfig
     * @param StoreManagerInterface $storeManager
     * @param FlagManager $flagManager
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ScopeConfigInterface   $scopeConfig,
        private readonly UrlInterface           $adminUrl,
        private readonly EncryptorInterface     $encryptor,
        private readonly Config                 $_resourceConfig,
        private readonly StoreManagerInterface  $storeManager,
        private readonly FlagManager            $flagManager,
        private readonly RequestInterface       $request,
        private readonly LoggerInterface        $logger,
    ) {
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->doGetShippingConfig('mode');
    }

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->doGetShippingConfig('client_id_' . $this->getMode());
    }

    /**
     * Get client secret
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->encryptor->decrypt(
            $this->doGetShippingConfig('client_secret_' . $this->getMode())
        );
    }

    /**
     * Get client secret
     *
     * @return string
     */
    public function getWellKnownUrl(): string
    {
        return $this->doGetShippingConfig('well_known_' . $this->getMode());
    }

    /**
     * Get client secret
     *
     * @return string|null
     */
    public function getRawAccessToken(): ?string
    {
        return $this->doGetShippingConfig(self::ACCESS_TOKEN);
    }

    /**
     * Get client secret
     *
     * @return string|null
     */
    public function getAccessTokenExpiresAt(): ?string
    {
        return $this->doGetShippingConfig(self::ACCESS_TOKEN_EXPIRES_AT);
    }

    /**
     * Get client secret
     *
     * @return string|null
     */
    public function getRawRefreshToken(): ?string
    {
        return $this->doGetShippingConfig(self::REFRESH_TOKEN);
    }

    /**
     * Get code verifier
     *
     * @return string
     */
    public function getCodeVerifier(): string
    {
        return $this->flagManager->getFlagData(self::CODE_VERIFIER);
    }

    /**
     * Get callback redirect uri
     *
     * @return string
     */
    public function getCallbackRedirectUri(): string
    {
        return $this->adminUrl->getUrl('inpostinternational/oauth/callback', ['_nosecret' => true]);
    }

    /**
     * Get weight attribute code
     */
    public function getWeightAttributeCode()
    {
        return $this->doGetShippingConfig('weight_attribute_code');
    }

    /**
     * Get weight unit
     */
    public function getWeightUnit()
    {
        return $this->doGetShippingConfig('weight_unit');
    }

    /**
     * Get configured shipping countries
     */
    public function getShippingCountries()
    {
        return $this->doGetCarriersConfig('specificcountry');
    }

    /**
     * Get default shipment type
     *
     * @return string
     */
    public function getShipmentType(): string
    {
        $sender = (array) $this->getSenderSettings();
        return (string) $sender['shipment_type'];
    }

    /**
     * Get sender settings
     *
     * @return array<string, mixed>
     */
    public function getSenderSettings(): array
    {
        return (array) $this->doGetShippingConfig('sender');
    }

    /**
     * Save code verifier
     *
     * @param string $codeVerifier
     * @return void
     */
    public function saveCodeVerifier(string $codeVerifier): void
    {
        // Flag manager is used because code verifier is needed in the callback action
        // while it is hard to read it from config in this case
        $this->flagManager->saveFlag(self::CODE_VERIFIER, $codeVerifier);
    }

    /**
     * Save access token
     *
     * @param mixed $accessToken
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveAccessToken(mixed $accessToken): void
    {
        $this->saveShippingConfig(self::ACCESS_TOKEN, $accessToken);
    }

    /**
     * Save access token expires at
     *
     * @param int $exp
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveAccessTokenExpiresAt(int $exp): void
    {
        $this->saveShippingConfig(self::ACCESS_TOKEN_EXPIRES_AT, $exp);
    }

    /**
     * Save refresh token
     *
     * @param mixed $refreshToken
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveRefreshToken(mixed $refreshToken): void
    {
        $this->saveShippingConfig(self::REFRESH_TOKEN, $refreshToken);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    private function doGetShippingConfig(string $path): mixed
    {
        try {
            list($scope, $scopeId) = $this->getCurrentScope();
            return $this->scopeConfig->getValue(
                self::SHIPPING_CONFIG_PATH . $path,
                $scope,
                $scopeId
            );
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }

    /**
     * Get carriers config
     *
     * @param string $path
     * @return mixed
     */
    private function doGetCarriersConfig(string $path): mixed
    {
        try {
            list($scope, $scopeId) = $this->getCurrentScope();
            return $this->scopeConfig->getValue(
                self::CARRIERS_CONFIG_PATH . $path,
                $scope,
                $scopeId
            );
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }

    /**
     * Save shipping config
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function saveShippingConfig(string $path, mixed $value): void
    {
        list($scope, $scopeId) = $this->getCurrentScope();
        $this->_resourceConfig->saveConfig(
            self::SHIPPING_CONFIG_PATH . $path,
            $value,
            $scope,
            $scopeId
        );
    }

    /**
     * Get current scope and scope id
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getCurrentScope(): array
    {
        $storeCode   = $this->request->getParam('store');
        $websiteCode = $this->request->getParam('website');
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;

        if ($storeCode) {
            $store   = $this->storeManager->getStore($storeCode);
            $scope   = ScopeInterface::SCOPE_STORE;
            $scopeId = $store->getId();
        } elseif ($websiteCode) {
            $website = $this->storeManager->getWebsite($websiteCode);
            $scope   = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $website->getId();
        }

        return [$scope, $scopeId];
    }
}
