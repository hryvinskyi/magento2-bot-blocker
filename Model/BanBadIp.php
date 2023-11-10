<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

class BanBadIp implements BanBadIpInterface
{
    private ResourceConnection $db;
    private IpStorageInterface $ipStorage;

    public function __construct(
        ResourceConnection $db,
        IpStorageInterface $ipStorage
    ) {
        $this->db = $db;
        $this->ipStorage = $ipStorage;
    }

    /**
     * @inheritDoc
     */
    public function banIp(string $ip, int $banTime): void
    {
        $banTime = time() + 900; // 900 seconds = 15 minutes
        $connection = $this->db->getConnection();
        $table = $connection->getTableName('hryvinskyi_bot_blocker_bans');

        $select = $connection->select()
            ->from($table)
            ->where('ip = ?', new Expression('INET6_ATON(\'' . $ip . '\')'));

        $result = $connection->fetchRow($select);

        if ($result === false) {
            $data = [
                'ip' => $this->ipStorage->pack($ip),
                'bans_count' => 1,
                'ban_expiration' => $banTime
            ];

            $connection->insert($table, $data);
        } else {
            $data = [
                'ip' => $this->ipStorage->pack($ip),
                'ban_expiration' => $result['bans_count'] * $banTime,
                'bans_count' => $result['bans_count'] + 1
            ];

            $where = ['ip = ?' => new Expression('INET6_ATON(\'' . $ip . '\')')];
            $connection->update($table, $data, $where);
        }
    }

    /**
     * @inheritDoc
     */
    public function checkIsBanned(string $ip): bool
    {
        $connection = $this->db->getConnection();
        $table = $connection->getTableName('hryvinskyi_bot_blocker_bans');

        $select = $connection->select()
            ->from($table)
            ->where('ip = ?', new Expression('INET6_ATON(\'' . $ip . '\')'));

        $result = $connection->fetchRow($select);

        if ($result === false) {
            return false;
        }

        if ($result['ban_expiration'] > time()) {
            return true;
        }

        return false;
    }
}
