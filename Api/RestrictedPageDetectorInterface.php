<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Api;

/**
 * Detects whether the current request targets a restricted page type
 * (e.g., 404, search results, filtered category pages).
 */
interface RestrictedPageDetectorInterface
{
    /**
     * Determine if the current request is for a restricted page.
     *
     * @return bool True if the current page is considered restricted.
     */
    public function isRestricted(): bool;
}
