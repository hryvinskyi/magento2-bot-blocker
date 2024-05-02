<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Validator;

use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;
use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\HandleStorage;

class ThresholdValidator implements ValidatorInterface
{
    private ConfigInterface $config;
    private HandleStorage $handleStorage;
    private BanBadIpInterface $banBadIp;

    public function __construct(
        ConfigInterface $config,
        HandleStorage $handleStorage,
        BanBadIpInterface $banBadIp
    ) {
        $this->config = $config;
        $this->handleStorage = $handleStorage;
        $this->banBadIp = $banBadIp;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $ipAddress): bool
    {
        $customerUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userAgentList = $this->config->getUserAgentOverride();

        $userAgentMatched = false;
        foreach ($userAgentList as $userAgentItem) {
            $userAgent = $userAgentItem['user_agent'];
            $threshold = $userAgentItem['threshold'];
            $timeframe = $userAgentItem['timeframe'];
            $banTime = $userAgentItem['block_time'];

            if (strpos($customerUserAgent, $userAgent) === false) {
                continue;
            }
            $userAgentMatched = true;
            if ($this->validateAndBanIp($threshold, $timeframe, $ipAddress, $banTime)) {
                return true;
            }
        }

        if ($userAgentMatched === false && $this->validateAndBanIp(
                $this->config->getThreshold(),
                $this->config->getTimeframe(),
                $ipAddress,
                $this->config->getBlockTime()
            )) {
            return true;
        }

        return false;
    }

    private function validateAndBanIp(int $threshold, int $timeframe, string $ipAddress, int $banTime): bool
    {
        $storageMethod = $this->config->getStorageMethod();

        $count = $this->handleStorage->execute($storageMethod, $ipAddress, $threshold, $timeframe);

        if ($count > $threshold) {
            $this->banBadIp->banIp($ipAddress, $banTime);
            return true;
        }

        return false;
    }
}