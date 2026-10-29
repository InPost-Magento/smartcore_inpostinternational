<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Service;

use Magento\Framework\Exception\LocalizedException;
use Smartcore\InPostInternational\Exception\ApiException;
use Smartcore\InPostInternational\Exception\TokenSaveException;
use Smartcore\InPostInternational\Model\Api\InternationalApiService;

class PickupPointService
{
    /**
     * @var array<string, array|null>
     */
    private array $points = [];

    /**
     * PickupPointService constructor.
     *
     * @param InternationalApiService $apiService
     */
    public function __construct(
        private readonly InternationalApiService $apiService,
    ) {
    }

    /**
     * Get pickup point details, or null if InPost doesn't recognise the point
     *
     * @param string $countryCode
     * @param string $pointName
     * @return array|null
     * @throws ApiException
     * @throws LocalizedException
     * @throws TokenSaveException
     */
    public function getPoint(string $countryCode, string $pointName): ?array
    {
        $key = $countryCode . '_' . $pointName;

        if (!array_key_exists($key, $this->points)) {
            try {
                $this->points[$key] = $this->apiService->getPoint($countryCode, $pointName);
            } catch (ApiException $e) {
                if (!in_array($e->getCode(), [400, 404], true)) {
                    throw $e;
                }

                $this->points[$key] = null;
            }
        }

        return $this->points[$key];
    }
}
