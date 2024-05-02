<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model\Validator;

use PHPUnit\Framework\TestCase;
use Hryvinskyi\BotBlocker\Model\Validator\IsBannedValidator;
use Hryvinskyi\BotBlocker\Model\BanBadIpInterface;

class IsBannedValidatorTest extends TestCase
{
    private $banBadIp;
    private $validator;

    protected function setUp(): void
    {
        $this->banBadIp = $this->createMock(BanBadIpInterface::class);
        $this->validator = new IsBannedValidator($this->banBadIp);
    }

    public function testValidate()
    {
        $this->banBadIp->method('checkIsBanned')->willReturn(true);

        $this->assertTrue($this->validator->validate('192.168.1.1'));
    }

    public function testValidateNegative()
    {
        $this->banBadIp->method('checkIsBanned')->willReturn(false);

        $this->assertFalse($this->validator->validate('192.168.1.1'));
    }
}