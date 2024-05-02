<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Config implements ConfigInterface
{
    public const XML_PATH_ENABLED = 'hryvinskyi_bot_blocker/general/enabled';
    public const XML_PATH_STORAGE_METHOD = 'hryvinskyi_bot_blocker/general/storage_method';
    public const XML_PATH_THRESHOLD = 'hryvinskyi_bot_blocker/general/threshold';
    public const XML_PATH_TIMEFRAME = 'hryvinskyi_bot_blocker/general/timeframe';
    public const XML_PATH_BLOCK_TIME = 'hryvinskyi_bot_blocker/general/block_time';
    public const XML_PATH_USER_AGENT_OVERRIDE = 'hryvinskyi_bot_blocker/general/user_agent_override';
    public const XML_PATH_WHITELIST = 'hryvinskyi_bot_blocker/general/whitelist';

    private ScopeConfigInterface $scopeConfig;
    private SerializerInterface $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getThreshold(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_THRESHOLD);
    }

    /**
     * @inheritDoc
     */
    public function getTimeframe(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_TIMEFRAME);
    }

    /**
     * @inheritDoc
     */
    public function getWhitelist(): array
    {
        $whitelist = $this->scopeConfig->getValue(self::XML_PATH_WHITELIST);
        return array_map('trim', array_filter(explode(',', (string)$whitelist)));
    }

    /**
     * @inheritDoc
     */
    public function getStorageMethod(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_STORAGE_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function getBlockTime(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_BLOCK_TIME);
    }

    /**
     * @inheritDoc
     */
    public function getUserAgentOverride(): array
    {
        $configJson = $this->scopeConfig->getValue(static::XML_PATH_USER_AGENT_OVERRIDE);
        try {
            return $this->serializer->unserialize($configJson);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}