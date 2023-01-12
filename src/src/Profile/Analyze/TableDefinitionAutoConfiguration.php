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

use Waldhacker\Pseudify\Core\Database\Schema;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

/**
 * @internal
 */
class TableDefinitionAutoConfiguration
{
    public function __construct(private Schema $schema)
    {
    }

    public function configure(TableDefinition $tableDefinition): TableDefinition
    {
        $this->removeNotExistingSourceTables($tableDefinition);
        $this->removeNotExistingTargetTables($tableDefinition);
        $this->removeNotExistingSourceColumns($tableDefinition);
        $this->removeNotExistingTargetColumns($tableDefinition);

        foreach ($this->schema->listTableNames() as $tableName) {
            $this->addTargetTableIfNotExists($tableDefinition, $tableName);
            $targetTable = $tableDefinition->getTargetTable($tableName);
            $this->addTargetColumnsIfNotExists($targetTable);

            if (empty($targetTable->getExcludedColumnTypes())) {
                $targetTable->excludeColumnTypes($tableDefinition->getExcludedTargetColumnTypes());
            }
            $this->removeExcludedTargetColumnsByType($targetTable);
            $this->removeExcludedTargetColumnsByIdentifier($targetTable);
        }

        $this->removeExcludedTargetTables($tableDefinition);
        $this->removeTargetColumnsBySourceColumns($tableDefinition);
        $this->removeSourceTablesWithEmptyColumns($tableDefinition);
        $this->removeTargetTablesWithEmptyColumns($tableDefinition);

        return $tableDefinition;
    }

    private function removeNotExistingSourceTables(TableDefinition $tableDefinition): void
    {
        $notExistingTables = array_diff(
            $tableDefinition->getSourceTableIdentifiers(),
            $this->schema->listTableNames()
        );
        foreach ($notExistingTables as $notExistingTable) {
            $tableDefinition->removeSourceTable($notExistingTable);
        }
    }

    private function removeNotExistingTargetTables(TableDefinition $tableDefinition): void
    {
        $notExistingTables = array_diff(
            $tableDefinition->getTargetTableIdentifiers(),
            $this->schema->listTableNames()
        );
        foreach ($notExistingTables as $notExistingTable) {
            $tableDefinition->removeTargetTable($notExistingTable);
        }
    }

    private function removeNotExistingSourceColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getSourceTables() as $table) {
            $this->removeNotExistingTableColumns($table);
        }
    }

    private function removeNotExistingTargetColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getTargetTables() as $table) {
            $this->removeNotExistingTableColumns($table);
        }
    }

    private function addTargetTableIfNotExists(TableDefinition $tableDefinition, string $tableName): void
    {
        if (!$tableDefinition->hasTargetTable($tableName)) {
            $tableDefinition->addTargetTable(TargetTable::create($tableName));
        }
    }

    private function addTargetColumnsIfNotExists(TargetTable $targetTable): void
    {
        foreach ($this->schema->listTableColumns($targetTable->getIdentifier()) as $column) {
            if (!$targetTable->hasColumn($column['name'])) {
                $targetTable->addColumn(TargetColumn::create($column['name']));
            }
        }
    }

    private function removeExcludedTargetColumnsByType(TargetTable $targetTable): void
    {
        $excludeColumns = array_filter(
            $this->schema->listTableColumns($targetTable->getIdentifier()),
            static fn (array $column): bool => in_array($column['column']->getType()->getName(), $targetTable->getExcludedColumnTypes(), true)
        );

        foreach ($excludeColumns as $column) {
            $targetTable->removeColumn($column['name']);
        }
    }

    private function removeExcludedTargetColumnsByIdentifier(TargetTable $targetTable): void
    {
        foreach ($targetTable->getExcludedColumnIdentifiers() as $identifier) {
            $targetTable->removeColumn($identifier);
        }
    }

    private function removeExcludedTargetTables(TableDefinition $tableDefinition): void
    {
        $excludeTargetTableNameExpressions = array_filter(array_map(
            static fn ($tableNameExpression): ?string => preg_replace(
                '/(?<!\\\\)#/',
                '\\#',
                $tableNameExpression
            ),
            $tableDefinition->getExcludedTargetTableIdentifiers()
        ));

        foreach ($this->schema->listTableNames() as $tableName) {
            foreach ($excludeTargetTableNameExpressions as $excludeTargetTableNameExpression) {
                if (preg_match('#^'.$excludeTargetTableNameExpression.'$#', $tableName, $matches)) {
                    $tableDefinition->removeTargetTable($tableName);
                    continue 2;
                }
            }
        }
    }

    private function removeTargetColumnsBySourceColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getSourceTables() as $sourceTable) {
            if (!$tableDefinition->hasTargetTable($sourceTable->getIdentifier())) {
                continue;
            }
            $targetTable = $tableDefinition->getTargetTable($sourceTable->getIdentifier());

            foreach ($sourceTable->getColumns() as $sourceColumn) {
                $targetTable->removeColumn($sourceColumn->getIdentifier());
            }
        }
    }

    private function removeSourceTablesWithEmptyColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getSourceTables() as $sourceTable) {
            if (!empty($sourceTable->getColumns())) {
                continue;
            }
            $tableDefinition->removeSourceTable($sourceTable->getIdentifier());
        }
    }

    private function removeTargetTablesWithEmptyColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getTargetTables() as $targetTable) {
            if (!empty($targetTable->getColumns())) {
                continue;
            }
            $tableDefinition->removeTargetTable($targetTable->getIdentifier());
        }
    }

    /**
     * @param SourceTable|TargetTable $table
     */
    private function removeNotExistingTableColumns($table): void
    {
        $existingTableColumns = array_map(
            static fn (array $column): string => $column['name'],
            $this->schema->listTableColumns($table->getIdentifier())
        );

        /** @var string[] $notExistingColumns */
        $notExistingColumns = array_diff(
            $table->getColumnIdentifiers(),
            $existingTableColumns
        );

        foreach ($notExistingColumns as $notExistingColumn) {
            $table->removeColumn($notExistingColumn);
        }
    }
}
