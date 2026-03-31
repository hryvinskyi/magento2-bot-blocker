<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Model\Validator;

use Hryvinskyi\BotBlocker\Api\RestrictedPageDetectorInterface;
use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Base class for page-type-specific rate limiting validators.
 * Provides shared request tracking logic via Redis-backed cache.
 */
abstract class AbstractPageTypeThresholdValidator implements ValidatorInterface
{
    private const CACHE_KEY_PREFIX = 'bb_rp_';
    private const CACHE_TAG = 'bb_restricted_page';

    private readonly BanBadIpInterface $banBadIp;
    private readonly CacheInterface $cache;
    private readonly SerializerInterface $serializer;

    /**
     * @param BanBadIpInterface $banBadIp
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        BanBadIpInterface $banBadIp,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->banBadIp = $banBadIp;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * Check if rate limiting is enabled for this page type.
     *
     * @return bool
     */
    abstract protected function isEnabled(): bool;

    /**
     * Get the request threshold for this page type.
     *
     * @return int
     */
    abstract protected function getThreshold(): int;

    /**
     * Get the timeframe for this page type.
     *
     * @return int Time window in seconds.
     */
    abstract protected function getTimeframe(): int;

    /**
     * Get the block time for this page type.
     *
     * @return int Ban duration in seconds.
     */
    abstract protected function getBlockTime(): int;

    /**
     * Get the unique key identifying this page type (used in cache keys).
     *
     * @return string
     */
    abstract protected function getTypeKey(): string;

    /**
     * Get the detector that identifies whether the current request matches this page type.
     *
     * @return RestrictedPageDetectorInterface
     */
    abstract protected function getDetector(): RestrictedPageDetectorInterface;

    /**
     * @inheritDoc
     */
    public function validate(string $ipAddress): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$this->getDetector()->isRestricted()) {
            return false;
        }

        $threshold = $this->getThreshold();
        $timeframe = $this->getTimeframe();

        $count = $this->trackRequest($ipAddress, $timeframe);

        if ($count > $threshold) {
            $this->banBadIp->banIp($ipAddress, $this->getBlockTime());
            return true;
        }

        return false;
    }

    /**
     * Track request count for this page type and IP.
     *
     * @param string $ipAddress The IP address to track.
     * @param int $timeframe The time window in seconds.
     *
     * @return int Current request count within the timeframe.
     */
    private function trackRequest(string $ipAddress, int $timeframe): int
    {
        $key = self::CACHE_KEY_PREFIX . $this->getTypeKey() . '_' . md5($ipAddress);
        $rawData = $this->cache->load($key);

        if ($rawData) {
            $data = $this->serializer->unserialize($rawData);
        } else {
            $data = [
                'count' => 0,
                'first_request_time' => time(),
            ];
        }

        $data['count']++;

        $elapsedTime = time() - $data['first_request_time'];
        if ($elapsedTime > $timeframe) {
            $data['count'] = 1;
            $data['first_request_time'] = time();
        }

        $this->cache->save(
            $this->serializer->serialize($data),
            $key,
            [self::CACHE_TAG],
            $timeframe
        );

        return (int)$data['count'];
    }
}
