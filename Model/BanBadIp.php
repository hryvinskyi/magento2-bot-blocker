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
        $connection = $this->db->getConnection();
        $table = $this->db->getTableName('hryvinskyi_bot_blocker_bans');
        $now = time();

        // The unique key on ip makes this a single atomic upsert; the previous select-then-write raced
        // with itself and left duplicate ban rows under exactly the traffic this module exists to stop.
        // ban_expiration is assigned before bans_count so that MySQL, applying the assignments left to
        // right, still sees the previous ban count and each repeat ban lasts proportionally longer.
        $connection->insertOnDuplicate(
            $table,
            [
                'ip' => $this->ipStorage->pack($ip),
                'bans_count' => 1,
                'ban_expiration' => $now + $banTime,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
            ],
            [
                'ban_expiration' => new Expression(sprintf('%d + bans_count * %d', $now, $banTime)),
                'bans_count' => new Expression('bans_count + 1'),
                'user_agent',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function checkIsBanned(string $ip): bool
    {
        $result = $this->selectBanByIp($ip);

        if ($result === null) {
            return false;
        }

        if ($result['ban_expiration'] > time()) {
            return true;
        }

        return false;
    }

    public function selectBanByIp(string $ip): ?array
    {
        $connection = $this->db->getConnection();
        $table = $this->db->getTableName('hryvinskyi_bot_blocker_bans');

        $select = $connection->select()
            ->from($table)
            ->where('ip = INET6_ATON(?)', $ip);

        $result = $connection->fetchRow($select);

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
