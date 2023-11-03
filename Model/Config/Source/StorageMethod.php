<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Config\Source;


use Magento\Framework\Data\OptionSourceInterface;

class StorageMethod implements OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'mysql', 'label' => __('MySQL')],
            ['value' => 'redis', 'label' => __('Redis')],
        ];
    }
}