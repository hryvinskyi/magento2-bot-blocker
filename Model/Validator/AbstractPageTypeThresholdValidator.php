<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Model\Validator;

use Hryvinskyi\BotBlocker\Api\RestrictedPageDetectorInterface;
use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;
use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\HandleStorage;

/**
 * Base class for page-type-specific rate limiting validators.
 * Uses the configured storage method (MySQL/Redis) via HandleStorage.
 */
abstract class AbstractPageTypeThresholdValidator implements ValidatorInterface
{
    private readonly BanBadIpInterface $banBadIp;
    private readonly ConfigInterface $config;
    private readonly HandleStorage $handleStorage;

    /**
     * @param BanBadIpInterface $banBadIp
     * @param ConfigInterface $config
     * @param HandleStorage $handleStorage
     */
    public function __construct(
        BanBadIpInterface $banBadIp,
        ConfigInterface $config,
        HandleStorage $handleStorage
    ) {
        $this->banBadIp = $banBadIp;
        $this->config = $config;
        $this->handleStorage = $handleStorage;
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
     * Get the unique key identifying this page type (used in storage keys).
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
        $storageMethod = $this->config->getStorageMethod();

        $count = $this->handleStorage->execute($storageMethod, $ipAddress, $threshold, $timeframe, $this->getTypeKey());

        if ($count > $threshold) {
            $this->banBadIp->banIp($ipAddress, $this->getBlockTime());
            return true;
        }

        return false;
    }
}
