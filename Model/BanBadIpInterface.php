<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

interface BanBadIpInterface
{
    /**
     * Bans the specified IP address for the given ban time.
     *
     * @param string $ip The IP address to ban.
     * @param int $banTime The ban time in seconds.
     * @return void
     */
    public function banIp(string $ip, int $banTime): void;

    /**
     * Checks whether the specified IP address is banned.
     *
     * @param string $ip The IP address to check.
     * @return bool Returns true if the IP address is banned, false otherwise.
     */
    public function checkIsBanned(string $ip): bool;
}