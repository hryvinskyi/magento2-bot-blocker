<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Validator;

use Hryvinskyi\BotBlocker\Model\ConfigInterface;

class IsWhitelistedValidator implements ValidatorInterface
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $ipAddress): bool
    {

        $isWhitelisted = in_array($ipAddress, $this->config->getWhitelist(), true);

        if ($isWhitelisted) {
            throw new SkipNextValidationsException();
        }

        return false;
    }
}