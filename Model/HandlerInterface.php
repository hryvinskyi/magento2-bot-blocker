<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

interface HandlerInterface
{
    /**
     * Executes a handler to save data to storage.
     *
     * @param string $ip The IP address to save.
     * @param int $threshold The threshold of requests.
     * @param int $timeframe The timeframe in seconds.
     *
     * @return int The number of requests from the IP address.
     */
    public function execute(string $ip, int $threshold, int $timeframe): int;
}