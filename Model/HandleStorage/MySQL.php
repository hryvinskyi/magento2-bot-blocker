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
    public function execute(string $ip, int $threshold, int $timeframe): int
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('hryvinskyi_bot_blocker_data');

        $now = time();

        $select = $connection->select()
            ->from($table)
            ->where('ip = ?', new Expression('INET6_ATON(\'' . $ip . '\')'));

        $result = $connection->fetchRow($select);

        if ($result === false) {
            $data = [
                'ip' => $this->ipStorage->pack($ip),
                'request_count' => 1,
                'first_request_time' => $now
            ];

            $connection->insert($table, $data);
        } else {
            $elapsedTime = $now - $result['first_request_time'];

            if ($elapsedTime > $timeframe) {
                // Reset the count and update the first request time
                $data = [
                    'request_count' => 1,
                    'first_request_time' => $now
                ];

                $where = ['ip = ?' => new Expression('INET6_ATON(\'' . $ip . '\')')];
                $connection->update($table, $data, $where);
            } else {
                // Increment the count
                $data = [
                    'request_count' => $result['request_count'] + 1,
                    'updated_at' => $now,
                ];

                $where = ['ip = ?' => new Expression('INET6_ATON(\'' . $ip . '\')')];
                $connection->update($table, $data, $where);
            }
        }

        return (int)$data['request_count'];
    }
}
