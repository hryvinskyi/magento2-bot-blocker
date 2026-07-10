<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\HandleStorage;

use Hryvinskyi\BotBlocker\Model\HandlerInterface;
use Hryvinskyi\BotBlocker\Model\IpStorageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

class MySQL implements HandlerInterface
{
    private ResourceConnection $resourceConnection;
    private IpStorageInterface $ipStorage;

    public function __construct(ResourceConnection $resourceConnection, IpStorageInterface $ipStorage)
    {
        $this->resourceConnection = $resourceConnection;
        $this->ipStorage = $ipStorage;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $ip, int $threshold, int $timeframe, string $type = 'general'): int
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('hryvinskyi_bot_blocker_data');

        $now = time();

        // A select followed by an insert lets two concurrent requests from the same IP both miss the
        // row and both insert one, which splits the counter and keeps the threshold out of reach.
        // The unique key on (ip, page_type) turns this into a single atomic upsert.
        // MySQL applies ON DUPLICATE KEY UPDATE assignments left to right, so request_count is still
        // computed against the previous first_request_time that the next assignment overwrites.
        // updated_at is left out on purpose: the column carries ON UPDATE CURRENT_TIMESTAMP.
        $connection->insertOnDuplicate(
            $table,
            [
                'ip' => $this->ipStorage->pack($ip),
                'page_type' => $type,
                'request_count' => 1,
                'first_request_time' => $now,
            ],
            [
                'request_count' => new Expression(
                    sprintf('IF(%d - first_request_time > %d, 1, request_count + 1)', $now, $timeframe)
                ),
                'first_request_time' => new Expression(
                    sprintf('IF(%d - first_request_time > %d, %d, first_request_time)', $now, $timeframe, $now)
                ),
            ]
        );

        $select = $connection->select()
            ->from($table, 'request_count')
            ->where('ip = INET6_ATON(?)', $ip)
            ->where('page_type = ?', $type);

        return (int)$connection->fetchOne($select);
    }
}
