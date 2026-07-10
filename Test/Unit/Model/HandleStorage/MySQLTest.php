<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Test\Unit\Model\HandleStorage;

use Hryvinskyi\BotBlocker\Model\HandleStorage\MySQL;
use Hryvinskyi\BotBlocker\Model\IpStorageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MySQLTest extends TestCase
{
    private const TABLE = 'data_table';

    private ResourceConnection|MockObject $resourceConnection;
    private IpStorageInterface|MockObject $ipStorage;
    private AdapterInterface|MockObject $connection;
    private Select|MockObject $select;
    private MySQL $storage;

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

        $this->storage = new MySQL($this->resourceConnection, $this->ipStorage);
    }

    /**
     * The counter must be bumped with one atomic upsert rather than a select-then-insert/update pair.
     *
     * @return void
     */
    public function testExecuteIssuesASingleUpsertAndReturnsTheStoredCount(): void
    {
        $this->connection->expects($this->never())->method('insert');
        $this->connection->expects($this->never())->method('update');

        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE,
                $this->callback(static function (array $data): bool {
                    return $data['ip'] === 'packedIp'
                        && $data['page_type'] === 'search'
                        && $data['request_count'] === 1
                        && is_int($data['first_request_time']);
                }),
                $this->anything()
            );

        $this->connection->method('fetchOne')->willReturn('7');

        $this->assertSame(7, $this->storage->execute('192.168.1.1', 5, 60, 'search'));
    }

    /**
     * updated_at carries ON UPDATE CURRENT_TIMESTAMP. Writing a Unix timestamp into that datetime
     * column raises "Incorrect datetime value" (MySQL error 1292) under STRICT_TRANS_TABLES.
     *
     * @return void
     */
    public function testExecuteNeverWritesUpdatedAt(): void
    {
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE,
                $this->callback(static function (array $data): bool {
                    return !array_key_exists('updated_at', $data);
                }),
                $this->callback(static function (array $fields): bool {
                    return !array_key_exists('updated_at', $fields);
                })
            );
        $this->connection->method('fetchOne')->willReturn('1');

        $this->storage->execute('192.168.1.1', 5, 60);
    }

    /**
     * request_count must be assigned before first_request_time, so the reset check still reads the
     * previous first_request_time that the following assignment overwrites.
     *
     * @return void
     */
    public function testRequestCountIsAssignedBeforeFirstRequestTime(): void
    {
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE,
                $this->anything(),
                $this->callback(static function (array $fields): bool {
                    if (array_keys($fields) !== ['request_count', 'first_request_time']) {
                        return false;
                    }

                    return (bool)preg_match(
                        '/^IF\(\d+ - first_request_time > 60, 1, request_count \+ 1\)$/',
                        (string)$fields['request_count']
                    ) && (bool)preg_match(
                        '/^IF\(\d+ - first_request_time > 60, \d+, first_request_time\)$/',
                        (string)$fields['first_request_time']
                    );
                })
            );
        $this->connection->method('fetchOne')->willReturn('1');

        $this->storage->execute('192.168.1.1', 5, 60);
    }

    /**
     * The IP must be bound as a parameter, never concatenated into the SQL string.
     *
     * @return void
     */
    public function testExecuteBindsTheIpAddress(): void
    {
        $this->connection->method('fetchOne')->willReturn('1');

        $this->storage->execute('192.168.1.1', 5, 60, 'search');

        $this->assertSame(
            [
                ['ip = INET6_ATON(?)', '192.168.1.1'],
                ['page_type = ?', 'search'],
            ],
            $this->whereCalls
        );
    }
}
