<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

interface ConfigInterface
{
    /**
     * Check if the BotBlocker module is enabled.
     *
     * @return bool True if the module is enabled, false otherwise.
     */
    public function isEnabled(): bool;

    /**
     * Get the storage method used by the BotBlocker module.
     *
     * @return string The storage method.
     */
    public function getStorageMethod(): string;

    /**
     * Get the threshold for the BotBlocker module.
     *
     * @return int The threshold.
     */
    public function getThreshold(): int;

    /**
     * Get the timeframe for the BotBlocker module.
     *
     * @return int The timeframe.
     */
    public function getTimeframe(): int;

    /**
     * Get the block time for the BotBlocker module.
     *
     * @return int The block time.
     */
    public function getBlockTime(): int;

    /**
     * Get the user agent override settings for the BotBlocker module.
     *
     * @return array The user agent override settings.
     */
    public function getUserAgentOverride(): array;

    /**
     * Get the whitelist for the BotBlocker module.
     *
     * @return array The whitelist.
     */
    public function getWhitelist(): array;
}