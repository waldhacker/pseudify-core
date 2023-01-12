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

namespace Waldhacker\Pseudify\Core\Profile\Pseudonymize;

use Waldhacker\Pseudify\Core\Database\Schema;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;

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
        $this->removeNotExistingTables($tableDefinition);
        $this->removeNotExistingColumns($tableDefinition);

        return $tableDefinition;
    }

    private function removeNotExistingTables(TableDefinition $tableDefinition): void
    {
        $notExistingTables = array_diff(
            $tableDefinition->getTableIdentifiers(),
            $this->schema->listTableNames()
        );
        foreach ($notExistingTables as $notExistingTable) {
            $tableDefinition->removeTable($notExistingTable);
        }
    }

    private function removeNotExistingColumns(TableDefinition $tableDefinition): void
    {
        foreach ($tableDefinition->getTables() as $table) {
            $this->removeNotExistingTableColumns($table);
        }
    }

    private function removeNotExistingTableColumns(Table $table): void
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
