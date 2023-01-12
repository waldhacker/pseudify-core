<?php

declare(strict_types=1);

/*
 * This file is part of the pseudify database pseudonymizer project
 * - (c) 2022 waldhacker UG (haftungsbeschränkt)
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Waldhacker\Pseudify\Core\Profile\Analyze;

use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Stats;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;

/**
 * @internal
 */
class Statistics
{
    public function __construct(private ConnectionManager $connectionManager)
    {
    }

    public function create(TableDefinition $tableDefinition): Stats
    {
        $sourceTableRowCount = [];
        $targetTableRowCount = [];
        $sourceTableColumnCount = [];
        $targetTableColumnCount = [];
        foreach ($tableDefinition->getSourceTables() as $table) {
            $sourceTableRowCount[$table->getIdentifier()] = $this->getRowCount($table->getIdentifier());
            $sourceTableColumnCount[$table->getIdentifier()] = count($table->getColumns());
        }
        foreach ($tableDefinition->getTargetTables() as $table) {
            $targetTableRowCount[$table->getIdentifier()] = $this->getRowCount($table->getIdentifier());
            $targetTableColumnCount[$table->getIdentifier()] = count($table->getColumns());
        }

        $stats = new Stats(
            $sourceTableRowCount,
            $sourceTableColumnCount,
            $targetTableRowCount,
            $targetTableColumnCount,
            !empty($tableDefinition->getSourceStrings())
        );

        return $stats;
    }

    private function getRowCount(string $tableName): int
    {
        $connection = $this->connectionManager->getConnection();
        $row = $connection->createQueryBuilder()
            ->select('COUNT(*) AS count')
            ->from($connection->quoteIdentifier($tableName))
            ->executeQuery()
            ->fetchAssociative();

        return false === $row ? 0 : (int) (is_int($row['count']) || is_string($row['count']) ? $row['count'] : 0);
    }
}
