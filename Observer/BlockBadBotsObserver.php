<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Observer;

use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\Validator\BotBlockerValidator;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Observes the `controller_action_predispatch` event.
 */
class BlockBadBotsObserver implements ObserverInterface
{
    private ConfigInterface $config;
    private BotBlockerValidator $botBlockerValidator;

    /**
     * @param ConfigInterface $config
     * @param BotBlockerValidator $botBlockerValidator
     */
    public function __construct(
        ConfigInterface $config,
        BotBlockerValidator $botBlockerValidator
    ) {
        $this->config = $config;
        $this->botBlockerValidator = $botBlockerValidator;
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
        if ($this->config->isEnabled() === false) {
            return;
        }

        if ($this->botBlockerValidator->validate($_SERVER['REMOTE_ADDR'])) {
            // Handle HTTP response in a separate class
            header('HTTP/1.0 429 Too Many Requests');
            exit();
        }
    }
}
