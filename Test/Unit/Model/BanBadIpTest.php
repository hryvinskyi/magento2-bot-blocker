<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Hryvinskyi\BotBlocker\Model\BanBadIp;
use Hryvinskyi\BotBlocker\Model\IpStorageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class BanBadIpTest extends TestCase
{
    private $resourceConnection;
    private $ipStorage;
    private $banBadIp;
    private $dbAdapter;

    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->ipStorage = $this->createMock(IpStorageInterface::class);
        $this->dbAdapter = $this->createMock(AdapterInterface::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->dbAdapter);
        $this->banBadIp = new BanBadIp($this->resourceConnection, $this->ipStorage);
    }

    public function banIpWhenIpIsNotBanned()
    {
        $this->ipStorage->method('pack')->willReturn('packedIp');
        $this->dbAdapter->method('getTableName')->willReturn('tableName');
        $this->dbAdapter->method('fetchRow')->willReturn(false);

        $this->dbAdapter->expects($this->once())
            ->method('insert')
            ->with(
                'tableName',
                $this->callback(function ($subject) {
                    return is_array($subject) && $subject['ip'] === 'packedIp';
                })
            );

        $this->banBadIp->banIp('192.168.1.1', 3600);
    }

    public function banIpWhenIpIsAlreadyBanned()
    {
        $this->ipStorage->method('pack')->willReturn('packedIp');
        $this->dbAdapter->method('getTableName')->willReturn('tableName');
        $this->dbAdapter->method('fetchRow')->willReturn(['ban_expiration' => time() - 3600, 'bans_count' => 1]);

        $this->dbAdapter->expects($this->once())
            ->method('update')
            ->with(
                'tableName',
                $this->callback(function ($subject) {
                    return is_array($subject) && $subject['ip'] === 'packedIp' && $subject['bans_count'] === 2;
                }),
                $this->callback(function ($subject) {
                    return is_array($subject) && $subject[0] === 'ip = ?';
                })
            );

        $this->banBadIp->banIp('192.168.1.1', 3600);
    }

    public function checkIsBannedWhenIpIsBanned()
    {
        $this->ipStorage->method('pack')->willReturn('packedIp');
        $this->dbAdapter->method('getTableName')->willReturn('tableName');
        $this->dbAdapter->method('fetchRow')->willReturn(['ban_expiration' => time() + 3600]);

        $this->assertTrue($this->banBadIp->checkIsBanned('192.168.1.1'));
    }

    public function checkIsBannedWhenIpIsNotBanned()
    {
        $this->ipStorage->method('pack')->willReturn('packedIp');
        $this->dbAdapter->method('getTableName')->willReturn('tableName');
        $this->dbAdapter->method('fetchRow')->willReturn(null);

        $this->assertFalse($this->banBadIp->checkIsBanned('192.168.1.1'));
    }

    public function checkIsBannedWhenBanIsExpired()
    {
        $this->ipStorage->method('pack')->willReturn('packedIp');
        $this->dbAdapter->method('getTableName')->willReturn('tableName');
        $this->dbAdapter->method('fetchRow')->willReturn(['ban_expiration' => time() - 3600]);

        $this->assertFalse($this->banBadIp->checkIsBanned('192.168.1.1'));
    }
}