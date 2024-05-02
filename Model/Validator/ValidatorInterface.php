<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Validator;

interface ValidatorInterface
{
    /**
     * Validate the IP address for bad bots.
     *
     * @param string $ipAddress
     * @return bool
     * @throws SkipNextValidationsException
     */
    public function validate(string $ipAddress): bool;
}