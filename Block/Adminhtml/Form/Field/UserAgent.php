<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class UserAgent extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('user_agent', ['label' => __('User Agent'), 'class' => 'required-entry']);
        $this->addColumn('threshold', ['label' => __('Threshold'), 'class' => 'required-entry', 'size' => 3]);
        $this->addColumn('timeframe', ['label' => __('Timeframe'), 'class' => 'required-entry', 'size' => 3]);
        $this->addColumn('block_time', ['label' => __('Block Time'), 'class' => 'required-entry', 'size' => 3]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $row->setData('option_extra_attrs', $options);
    }
}
