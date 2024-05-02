<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model\Validator;

use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\Validator\IsWhitelistedValidator;
use Hryvinskyi\BotBlocker\Model\Validator\SkipNextValidationsException;
use PHPUnit\Framework\TestCase;
class IsWhitelistedValidatorTest extends TestCase
{
    private $configMock;
    private $isWhitelistedValidator;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->isWhitelistedValidator = new IsWhitelistedValidator($this->configMock);
    }

    public function testValidateWithWhitelistedIp(): void
    {
        $this->configMock->method('getWhitelist')->willReturn(['192.168.1.1', '192.168.1.2']);

        $this->expectException(SkipNextValidationsException::class);
        $this->isWhitelistedValidator->validate('192.168.1.1');
    }

    public function testValidateWithNonWhitelistedIp(): void
    {
        $this->configMock->method('getWhitelist')->willReturn(['192.168.1.1']);
        $this->assertFalse($this->isWhitelistedValidator->validate('192.168.1.2'));
    }
}