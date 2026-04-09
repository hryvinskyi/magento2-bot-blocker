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
 * Rate limiting validator for 404 (no route) pages.
 */
class NoRouteThresholdValidator extends AbstractPageTypeThresholdValidator
{
    private readonly ConfigInterface $config;
    private readonly RestrictedPageDetectorInterface $detector;

    /**
     * @param BanBadIpInterface $banBadIp
     * @param ConfigInterface $config
     * @param HandleStorage $handleStorage
     * @param RestrictedPageDetectorInterface $detector
     */
    public function __construct(
        BanBadIpInterface $banBadIp,
        ConfigInterface $config,
        HandleStorage $handleStorage,
        RestrictedPageDetectorInterface $detector
    ) {
        parent::__construct($banBadIp, $config, $handleStorage);
        $this->config = $config;
        $this->detector = $detector;
    }

    /**
     * @inheritDoc
     */
    protected function isEnabled(): bool
    {
        return $this->config->isNoRouteRateLimitEnabled();
    }

    /**
     * @inheritDoc
     */
    protected function getThreshold(): int
    {
        return $this->config->getNoRouteThreshold();
    }

    /**
     * @inheritDoc
     */
    protected function getTimeframe(): int
    {
        return $this->config->getNoRouteTimeframe();
    }

    /**
     * @inheritDoc
     */
    protected function getBlockTime(): int
    {
        return $this->config->getNoRouteBlockTime();
    }

    /**
     * @inheritDoc
     */
    protected function getTypeKey(): string
    {
        return 'no_route';
    }

    /**
     * @inheritDoc
     */
    protected function getDetector(): RestrictedPageDetectorInterface
    {
        return $this->detector;
    }
}
