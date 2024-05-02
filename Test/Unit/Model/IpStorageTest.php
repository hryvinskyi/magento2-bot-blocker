<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model;

use Hryvinskyi\BotBlocker\Model\IpStorage;
use PHPUnit\Framework\TestCase;

class IpStorageTest extends TestCase
{
    public function testPackValidIpv4Address()
    {
        $ipStorage = new IpStorage();
        $packedIp = $ipStorage->pack('192.168.1.1');
        $this->assertEquals(inet_pton('192.168.1.1'), $packedIp);
    }

    public function testPackValidIpv6Address()
    {
        $ipStorage = new IpStorage();
        $packedIp = $ipStorage->pack('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertEquals(inet_pton('2001:0db8:85a3:0000:0000:8a2e:0370:7334'), $packedIp);
    }

    public function testUnpackValidPackedIpv4Address()
    {
        $ipStorage = new IpStorage();
        $ip = $ipStorage->unpack(inet_pton('192.168.1.1'));
        $this->assertEquals('192.168.1.1', $ip);
    }

    public function testUnpackValidPackedIpv6Address()
    {
        $ipStorage = new IpStorage();
        $ip = $ipStorage->unpack(inet_pton('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertEquals(inet_ntop(inet_pton('2001:0db8:85a3:0000:0000:8a2e:0370:7334')), $ip);
    }

    public function testUnpackInvalidPackedIpAddress()
    {
        $this->expectException(\Exception::class);
        $ipStorage = new IpStorage();
        $ipStorage->unpack('invalidPackedIp');
    }
}
