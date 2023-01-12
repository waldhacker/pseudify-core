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

namespace Waldhacker\Pseudify\Core\Database;

use Doctrine\DBAL\Schema\Column;

/**
 * @internal
 */
class Schema
{
    public function __construct(
        private ConnectionManager $connectionManager,
        private Query $query
    ) {
    }

    /**
     * @return string[]
     */
    public function listTableNames(): array
    {
        $connection = $this->connectionManager->getConnection();

        return array_map(
            fn (string $table): string => $this->query->unquoteSingleIdentifier($table),
            $connection->createSchemaManager()->listTableNames()
        );
    }

    /**
     * @return array<array-key, array{name: string, column: Column}>
     */
    public function listTableColumns(string $table): array
    {
        $connection = $this->connectionManager->getConnection();

        return array_map(
            /*
             * @return array{name: string, column: Column}
             */
            fn (Column $column): array => [
                'name' => $this->query->unquoteSingleIdentifier($column->getName()),
                'column' => $column,
            ],
            $connection->createSchemaManager()->listTableColumns($table)
        );
    }

    /**
     * @return array{name: string, column: Column}
     */
    public function getColumn(string $table, string $identifier): array
    {
        $column = array_values(array_filter(
            $this->listTableColumns($table),
            /**
             * @param array{name: string, column: Column} $column
             */
            static fn (array $column): bool => $column['name'] === $identifier
        ))[0] ?? null;

        if (empty($column)) {
            throw new MissingColumnException(sprintf('missing column "%s" for table "%s"', $identifier, $table), 1621654988);
        }

        return $column;
    }
}
