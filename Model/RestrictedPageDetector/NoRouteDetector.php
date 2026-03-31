<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Model\RestrictedPageDetector;

use Hryvinskyi\BotBlocker\Api\RestrictedPageDetectorInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Detects 404/no-route pages by checking the dispatched controller action.
 */
class NoRouteDetector implements RestrictedPageDetectorInterface
{
    private readonly RequestInterface $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function isRestricted(): bool
    {
        $fullActionName = strtolower(
            $this->request->getModuleName()
            . '_' . $this->request->getControllerName()
            . '_' . $this->request->getActionName()
        );

        return $fullActionName === 'cms_noroute_index';
    }
}
