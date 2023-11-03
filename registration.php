<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

use Magento\Framework\Component\ComponentRegistrar;

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Hryvinskyi_BotBlocker', __DIR__);
