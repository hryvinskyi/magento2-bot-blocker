<?php
/**
 * Copyright (c) 2026. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\BotBlocker\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Collapses duplicate rows and makes the IP lookup keys unique.
 *
 * The unique keys cannot live in db_schema.xml: declarative schema is applied before any patch runs,
 * so ADD UNIQUE would abort with a duplicate-key error on the very installations that need cleaning.
 * Because the keys are absent from db_schema.xml and from db_schema_whitelist.json, declarative schema
 * treats them as unmanaged and never drops them again.
 */
class ConvertIpIndexesToUnique implements SchemaPatchInterface
{
    private const DATA_TABLE = 'hryvinskyi_bot_blocker_data';
    private const BANS_TABLE = 'hryvinskyi_bot_blocker_bans';
    private const DATA_INDEX = 'HRYVINSKYI_BOT_BLOCKER_DATA_IP_PAGE_TYPE';
    private const BANS_INDEX = 'HRYVINSKYI_BOT_BLOCKER_BANS_IP';

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(private readonly SchemaSetupInterface $schemaSetup)
    {
    }

    /**
     * Remove duplicate rows, then promote both IP lookup keys to unique keys.
     *
     * @return self
     */
    public function apply(): self
    {
        $this->schemaSetup->startSetup();

        $connection = $this->schemaSetup->getConnection();
        $dataTable = $this->schemaSetup->getTable(self::DATA_TABLE);
        $bansTable = $this->schemaSetup->getTable(self::BANS_TABLE);

        $this->removeDuplicateRequestCounters($dataTable);
        $this->removeDuplicateBans($bansTable);

        $connection->addIndex(
            $dataTable,
            self::DATA_INDEX,
            ['ip', 'page_type'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
        $connection->addIndex($bansTable, self::BANS_INDEX, ['ip'], AdapterInterface::INDEX_TYPE_UNIQUE);

        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * Keep one counter per (ip, page_type): the highest request count, oldest row on a tie.
     *
     * @param string $table
     * @return void
     */
    private function removeDuplicateRequestCounters(string $table): void
    {
        $connection = $this->schemaSetup->getConnection();
        $quoted = $connection->quoteIdentifier($table);

        $connection->query(
            sprintf(
                'DELETE t FROM %1$s t INNER JOIN %1$s k'
                . ' ON t.ip = k.ip AND t.page_type = k.page_type'
                . ' AND (k.request_count > t.request_count'
                . ' OR (k.request_count = t.request_count AND k.id < t.id))',
                $quoted
            )
        );
    }

    /**
     * Keep one ban per ip: the one expiring last, then the highest ban count, oldest row on a tie.
     *
     * @param string $table
     * @return void
     */
    private function removeDuplicateBans(string $table): void
    {
        $connection = $this->schemaSetup->getConnection();
        $quoted = $connection->quoteIdentifier($table);

        $connection->query(
            sprintf(
                'DELETE t FROM %1$s t INNER JOIN %1$s k ON t.ip = k.ip'
                . ' AND (k.ban_expiration > t.ban_expiration'
                . ' OR (k.ban_expiration = t.ban_expiration AND k.bans_count > t.bans_count)'
                . ' OR (k.ban_expiration = t.ban_expiration AND k.bans_count = t.bans_count'
                . ' AND k.id < t.id))',
                $quoted
            )
        );
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
