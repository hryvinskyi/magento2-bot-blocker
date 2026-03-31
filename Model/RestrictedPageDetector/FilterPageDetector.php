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
 * Detects filtered category/catalog pages by checking for layered navigation
 * query parameters in the request.
 */
class FilterPageDetector implements RestrictedPageDetectorInterface
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

        if ($fullActionName !== 'catalog_category_view') {
            return false;
        }

        $queryParams = $this->request->getParams();
        unset($queryParams['id'], $queryParams['p'], $queryParams['product_list_order'], $queryParams['product_list_dir'], $queryParams['product_list_limit']);

        return count($queryParams) > 0;
    }
}
