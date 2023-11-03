<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const XML_PATH_ENABLED = 'hryvinskyi_bot_blocker/general/enabled';
    public const XML_PATH_THRESHOLD = 'hryvinskyi_bot_blocker/general/threshold';
    public const XML_PATH_TIMEFRAME = 'hryvinskyi_bot_blocker/general/timeframe';
    public const XML_PATH_WHITELIST = 'hryvinskyi_bot_blocker/general/whitelist';
    public const XML_PATH_STORAGE_METHOD = 'hryvinskyi_bot_blocker/general/storage_method';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    public function getThreshold(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_THRESHOLD);
    }

    public function getTimeframe(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_TIMEFRAME);
    }

    public function getWhitelist(): array
    {
        $whitelist = $this->scopeConfig->getValue(self::XML_PATH_WHITELIST);
        return array_filter(explode(',', (string)$whitelist));
    }

    public function getStorageMethod(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_STORAGE_METHOD);
    }
}