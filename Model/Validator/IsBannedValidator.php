<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Validator;

use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;

class IsBannedValidator implements ValidatorInterface
{
    private BanBadIpInterface $banBadIp;

    public function __construct(BanBadIpInterface $banBadIp)
    {
        $this->banBadIp = $banBadIp;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $ipAddress): bool
    {
        return $this->banBadIp->checkIsBanned($ipAddress);
    }
}