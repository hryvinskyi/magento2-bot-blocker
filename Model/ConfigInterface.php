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

    /**
     * Check if 404 (no route) page rate limiting is enabled.
     *
     * @return bool
     */
    public function isNoRouteRateLimitEnabled(): bool;

    /**
     * Get the request threshold for 404 (no route) pages.
     *
     * @return int
     */
    public function getNoRouteThreshold(): int;

    /**
     * Get the timeframe for 404 (no route) page rate limiting.
     *
     * @return int The time window in seconds.
     */
    public function getNoRouteTimeframe(): int;

    /**
     * Get the block time for 404 (no route) pages.
     *
     * @return int The ban duration in seconds.
     */
    public function getNoRouteBlockTime(): int;

    /**
     * Check if search page rate limiting is enabled.
     *
     * @return bool
     */
    public function isSearchPageRateLimitEnabled(): bool;

    /**
     * Get the request threshold for search pages.
     *
     * @return int
     */
    public function getSearchPageThreshold(): int;

    /**
     * Get the timeframe for search page rate limiting.
     *
     * @return int The time window in seconds.
     */
    public function getSearchPageTimeframe(): int;

    /**
     * Get the block time for search pages.
     *
     * @return int The ban duration in seconds.
     */
    public function getSearchPageBlockTime(): int;

    /**
     * Check if filter page rate limiting is enabled.
     *
     * @return bool
     */
    public function isFilterPageRateLimitEnabled(): bool;

    /**
     * Get the request threshold for filter pages.
     *
     * @return int
     */
    public function getFilterPageThreshold(): int;

    /**
     * Get the timeframe for filter page rate limiting.
     *
     * @return int The time window in seconds.
     */
    public function getFilterPageTimeframe(): int;

    /**
     * Get the block time for filter pages.
     *
     * @return int The ban duration in seconds.
     */
    public function getFilterPageBlockTime(): int;
}