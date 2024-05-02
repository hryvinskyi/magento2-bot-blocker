<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model\Validator;

use PHPUnit\Framework\TestCase;
use Hryvinskyi\BotBlocker\Model\Validator\ThresholdValidator;
use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\HandleStorage;
use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;

class ThresholdValidatorTest extends TestCase
{
    private $config;
    private $handleStorage;
    private $banBadIp;
    private $validator;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->handleStorage = $this->createMock(HandleStorage::class);
        $this->banBadIp = $this->createMock(BanBadIpInterface::class);
        $this->validator = new ThresholdValidator($this->config, $this->handleStorage, $this->banBadIp);
    }

    public function testValidate()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'testUserAgent';
        $this->config->method('getUserAgentOverride')->willReturn([
            [
                'user_agent' => 'testUserAgent',
                'threshold' => 5,
                'timeframe' => 60,
                'block_time' => 3600
            ]
        ]);

        $this->handleStorage->method('execute')->willReturn(6);
        $this->banBadIp->expects($this->once())->method('banIp');

        $this->assertTrue($this->validator->validate('192.168.1.1'));
    }

    public function testValidateNegative()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'testUserAgent';
        $this->config->expects($this->once())->method('getThreshold')->willReturn(5);
        $this->config->expects($this->once())->method('getUserAgentOverride')->willReturn([
            [
                'user_agent' => 'testUserAgent',
                'threshold' => 5,
                'timeframe' => 60,
                'block_time' => 3600
            ]
        ]);

        $this->handleStorage->method('execute')->willReturn(4);
        $this->banBadIp->expects($this->never())->method('banIp');

        $this->assertFalse($this->validator->validate('192.168.1.1'));
    }

    public function testValidateWithDifferentUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'differentUserAgent';
        $this->config->expects($this->once())->method('getThreshold')->willReturn(5);
        $this->config->expects($this->once())->method('getUserAgentOverride')->willReturn([
            [
                'user_agent' => 'testUserAgent',
                'threshold' => 5,
                'timeframe' => 60,
                'block_time' => 3600
            ]
        ]);

        $this->handleStorage->method('execute')->willReturn(4);
        $this->banBadIp->expects($this->never())->method('banIp');

        $this->assertFalse($this->validator->validate('192.168.1.1'));
    }
}