<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model;

use Hryvinskyi\BotBlocker\Model\BanBadIp;
use Hryvinskyi\BotBlocker\Model\IpStorageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BanBadIpTest extends TestCase
{
    private const TABLE = 'bans_table';

    private ResourceConnection|MockObject $resourceConnection;
    private IpStorageInterface|MockObject $ipStorage;
    private AdapterInterface|MockObject $connection;
    private Select|MockObject $select;
    private BanBadIp $banBadIp;

    /**
     * Every where() call made on the select, captured as [condition, value] pairs.
     *
     * @var array<int, array<int, mixed>>
     */
    private array $whereCalls = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->ipStorage = $this->createMock(IpStorageInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);

        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->willReturn(self::TABLE);
        $this->connection->method('select')->willReturn($this->select);
        $this->select->method('from')->willReturnSelf();
        $this->select->method('where')->willReturnCallback(function (...$args): Select {
            // where() is declared as where($cond, $value = null, $type = null); only the first two matter.
            $this->whereCalls[] = array_slice($args, 0, 2);

            return $this->select;
        });
        $this->ipStorage->method('pack')->willReturn('packedIp');

        $this->banBadIp = new BanBadIp($this->resourceConnection, $this->ipStorage);
    }

    /**
     * A ban must be written with one atomic upsert, never a select-then-insert/update pair.
     *
     * @return void
     */
    public function testBanIpIssuesASingleUpsert(): void
    {
        $this->connection->expects($this->never())->method('insert');
        $this->connection->expects($this->never())->method('update');

        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE,
                $this->callback(static function (array $data): bool {
                    return $data['ip'] === 'packedIp'
                        && $data['bans_count'] === 1
                        && is_int($data['ban_expiration']);
                }),
                $this->anything()
            );

        $this->banBadIp->banIp('192.168.1.1', 3600);
    }

    /**
     * ban_expiration must be assigned before bans_count, so the expiration is still derived from the
     * previous ban count and each repeat ban lasts proportionally longer.
     *
     * @return void
     */
    public function testBanExpirationIsAssignedBeforeBanCountIsIncremented(): void
    {
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE,
                $this->anything(),
                $this->callback(static function (array $fields): bool {
                    $keys = array_keys($fields);

                    return $keys === ['ban_expiration', 'bans_count', 0]
                        && (string)$fields['bans_count'] === 'bans_count + 1'
                        && (bool)preg_match('/^\d+ \+ bans_count \* 3600$/', (string)$fields['ban_expiration'])
                        && $fields[0] === 'user_agent';
                })
            );

        $this->banBadIp->banIp('192.168.1.1', 3600);
    }

    /**
     * The IP must be bound as a parameter, never concatenated into the SQL string.
     *
     * @return void
     */
    public function testSelectBanByIpBindsTheIpAddress(): void
    {
        $this->connection->method('fetchRow')->willReturn(false);

        $this->assertNull($this->banBadIp->selectBanByIp('192.168.1.1'));
        $this->assertSame([['ip = INET6_ATON(?)', '192.168.1.1']], $this->whereCalls);
    }

    /**
     * @return void
     */
    public function testCheckIsBannedReturnsTrueWhileTheBanIsActive(): void
    {
        $this->connection->method('fetchRow')->willReturn(['ban_expiration' => time() + 3600]);

        $this->assertTrue($this->banBadIp->checkIsBanned('192.168.1.1'));
    }

    /**
     * @return void
     */
    public function testCheckIsBannedReturnsFalseOnceTheBanExpired(): void
    {
        $this->connection->method('fetchRow')->willReturn(['ban_expiration' => time() - 3600]);

        $this->assertFalse($this->banBadIp->checkIsBanned('192.168.1.1'));
    }

    /**
     * @return void
     */
    public function testCheckIsBannedReturnsFalseWhenTheIpWasNeverBanned(): void
    {
        $this->connection->method('fetchRow')->willReturn(false);

        $this->assertFalse($this->banBadIp->checkIsBanned('192.168.1.1'));
    }
}
