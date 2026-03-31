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
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Rate limiting validator for filtered category pages (layered navigation).
 */
class FilterPageThresholdValidator extends AbstractPageTypeThresholdValidator
{
    private readonly ConfigInterface $config;
    private readonly RestrictedPageDetectorInterface $detector;

    /**
     * @param BanBadIpInterface $banBadIp
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param ConfigInterface $config
     * @param RestrictedPageDetectorInterface $detector
     */
    public function __construct(
        BanBadIpInterface $banBadIp,
        CacheInterface $cache,
        SerializerInterface $serializer,
        ConfigInterface $config,
        RestrictedPageDetectorInterface $detector
    ) {
        parent::__construct($banBadIp, $cache, $serializer);
        $this->config = $config;
        $this->detector = $detector;
    }

    /**
     * @inheritDoc
     */
    protected function isEnabled(): bool
    {
        return $this->config->isFilterPageRateLimitEnabled();
    }

    /**
     * @inheritDoc
     */
    protected function getThreshold(): int
    {
        return $this->config->getFilterPageThreshold();
    }

    /**
     * @inheritDoc
     */
    protected function getTimeframe(): int
    {
        return $this->config->getFilterPageTimeframe();
    }

    /**
     * @inheritDoc
     */
    protected function getBlockTime(): int
    {
        return $this->config->getFilterPageBlockTime();
    }

    /**
     * @inheritDoc
     */
    protected function getTypeKey(): string
    {
        return 'filter_page';
    }

    /**
     * @inheritDoc
     */
    protected function getDetector(): RestrictedPageDetectorInterface
    {
        return $this->detector;
    }
}
