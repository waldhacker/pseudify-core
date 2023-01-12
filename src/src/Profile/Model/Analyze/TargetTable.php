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

namespace Waldhacker\Pseudify\Core\Profile\Model\Analyze;

class TargetTable
{
    /** @var array<string, TargetColumn> */
    private array $columns = [];
    /** @var array<string, TargetColumn> */
    private array $excludedColumns = [];
    /** @var array<string, string> */
    private array $excludedColumnTypes = [];

    /**
     * @param array<int, string|TargetColumn> $columns
     * @param array<int, string|TargetColumn> $excludeColumns
     * @param array<string, string>           $excludeColumnTypes
     *
     * @internal
     */
    public function __construct(private string $identifier, array $columns = [], array $excludeColumns = [], array $excludeColumnTypes = [])
    {
        $this->addColumns($columns);
        $this->excludeColumns($excludeColumns);
        $this->excludeColumnTypes($excludeColumnTypes);
    }

    /**
     * @param array<int, string|TargetColumn> $columns
     * @param array<int, string|TargetColumn> $excludeColumns
     * @param array<string, string>           $excludeColumnTypes
     *
     * @api
     */
    public static function create(string $identifier, array $columns = [], array $excludeColumns = [], array $excludeColumnTypes = []): TargetTable
    {
        return new self($identifier, $columns, $excludeColumns, $excludeColumnTypes);
    }

    /**
     * @api
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @api
     */
    public function hasColumn(string $identifier): bool
    {
        return isset($this->columns[$identifier]);
    }

    /**
     * @api
     */
    public function getColumn(string $identifier): TargetColumn
    {
        if (!$this->hasColumn($identifier)) {
            throw new MissingTargetColumnException(sprintf('missing target column "%s" for table "%s"', $identifier, $this->identifier), 1621654997);
        }

        return $this->columns[$identifier];
    }

    /**
     * @param array<int, string|TargetColumn> $columns
     *
     * @api
     */
    public function addColumns(array $columns): TargetTable
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    /**
     * @api
     */
    public function addColumn(TargetColumn|string $column): TargetTable
    {
        $column = is_string($column) ? TargetColumn::create($column) : $column;
        $this->columns[$column->getIdentifier()] = $column;

        return $this;
    }

    /**
     * @api
     */
    public function removeColumn(string $identifier): TargetTable
    {
        unset($this->columns[$identifier]);

        return $this;
    }

    /**
     * @return array<string, TargetColumn>
     *
     * @api
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<int, string>
     */
    public function getColumnIdentifiers(): array
    {
        return array_keys($this->columns);
    }

    /**
     * @api
     */
    public function isColumnExcluded(string $identifier): bool
    {
        return isset($this->excludedColumns[$identifier]);
    }

    /**
     * @api
     */
    public function getExcludedColumn(string $identifier): TargetColumn
    {
        if (!$this->isColumnExcluded($identifier)) {
            throw new MissingExcludedColumnException(sprintf('missing excluded column "%s" for table "%s"', $identifier, $this->identifier), 1621654998);
        }

        return $this->excludedColumns[$identifier];
    }

    /**
     * @param array<int, string|TargetColumn> $columns
     *
     * @api
     */
    public function excludeColumns(array $columns): TargetTable
    {
        foreach ($columns as $column) {
            $this->excludeColumn($column);
        }

        return $this;
    }

    /**
     * @api
     */
    public function excludeColumn(TargetColumn|string $column): TargetTable
    {
        $column = is_string($column) ? TargetColumn::create($column) : $column;
        $this->excludedColumns[$column->getIdentifier()] = $column;

        return $this;
    }

    /**
     * @api
     */
    public function removeExcludedColumn(string $identifier): TargetTable
    {
        unset($this->excludedColumns[$identifier]);

        return $this;
    }

    /**
     * @return array<string, TargetColumn>
     *
     * @api
     */
    public function getExcludedColumns(): array
    {
        return $this->excludedColumns;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getExcludedColumnIdentifiers(): array
    {
        return array_keys($this->excludedColumns);
    }

    /**
     * @api
     */
    public function isTargetColumnTypeExcluded(string $columnType): bool
    {
        return isset($this->excludedColumnTypes[$columnType]);
    }

    /**
     * @param array<string, string> $columnTypes
     *
     * @api
     */
    public function excludeColumnTypes(array $columnTypes): TargetTable
    {
        foreach ($columnTypes as $columnType) {
            $this->excludeColumnType($columnType);
        }

        return $this;
    }

    /**
     * @api
     */
    public function excludeColumnType(string $columnType): TargetTable
    {
        $this->excludedColumnTypes[$columnType] = $columnType;

        return $this;
    }

    /**
     * @api
     */
    public function removeExcludedColumnType(string $columnType): TargetTable
    {
        unset($this->excludedColumnTypes[$columnType]);

        return $this;
    }

    /**
     * @return array<string, string>
     *
     * @api
     */
    public function getExcludedColumnTypes(): array
    {
        return $this->excludedColumnTypes;
    }
}
