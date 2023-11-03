<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

class HandleStorage
{
    private array $handlers;

    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * Retrieves the handler for the specified method.
     *
     * @param string $method The method for which to retrieve the handler.
     *
     * @return HandlerInterface The handler for the specified method.
     *
     * @throws \InvalidArgumentException if the handler for the specified method is not found.
     */
    public function getHandler(string $method): HandlerInterface
    {
        if (!isset($this->handlers[$method])) {
            throw new \InvalidArgumentException(sprintf('Handler for method "%s" not found', $method));
        }

        return $this->handlers[$method];
    }

    /**
     * Executes a method on the handler based on the provided parameters.
     *
     * @param string $method The method to execute on the handler.
     * @param string $ip The IP address to apply the method on.
     * @param int $threshold The threshold value to be used by the method.
     * @param int $timeframe The timeframe value to be used by the method.
     *
     * @return int The result of executing the method on the handler.
     */
    public function execute(string $method, string $ip, int $threshold, int $timeframe): int
    {
        return $this->getHandler($method)->execute($ip, $threshold, $timeframe);
    }
}