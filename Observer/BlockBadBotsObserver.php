<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Observer;

use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;
use Hryvinskyi\BotBlocker\Model\Config;
use Hryvinskyi\BotBlocker\Model\HandleStorage;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Observes the `controller_action_predispatch` event.
 */
class BlockBadBotsObserver implements ObserverInterface
{
    private Config $config;
    private HandleStorage $handleStorage;
    private BanBadIpInterface $banBadIp;

    public function __construct(
        Config $config,
        HandleStorage $handleStorage,
        BanBadIpInterface $banBadIp
    ) {
        $this->config = $config;
        $this->handleStorage = $handleStorage;
        $this->banBadIp = $banBadIp;
    }

    /**
     * Observer for controller_action_predispatch.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $threshold = $this->config->getThreshold();
        $timeframe = $this->config->getTimeframe();
        $whitelist = $this->config->getWhitelist();
        $storageMethod = $this->config->getStorageMethod();

        // Check if module disabled or IP address is in the whitelist
        if ($this->config->isEnabled() === false || in_array($ipAddress, $whitelist, true)) {
            return;
        }

        if ($this->banBadIp->checkIsBanned($ipAddress)) {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }

        $count = $this->handleStorage->execute($storageMethod, $ipAddress, $threshold, $timeframe);

        if ($count > $threshold) {
            $this->banBadIp->banIp($ipAddress, 900);
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
    }
}
