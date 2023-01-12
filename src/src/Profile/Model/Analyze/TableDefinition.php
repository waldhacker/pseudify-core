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

use Doctrine\DBAL\Types\Types;

class TableDefinition
{
    public const COMMON_EXCLUED_TARGET_COLUMN_TYPES = [
        Types::BIGINT,
        Types::BOOLEAN,
        Types::DATE_MUTABLE,
        Types::DATE_IMMUTABLE,
        Types::DATEINTERVAL,
        Types::DATETIME_MUTABLE,
        Types::DATETIME_IMMUTABLE,
        Types::DATETIMETZ_MUTABLE,
        Types::DATETIMETZ_IMMUTABLE,
        Types::DECIMAL,
        Types::FLOAT,
        Types::GUID,
        Types::INTEGER,
        Types::SMALLINT,
        Types::TIME_MUTABLE,
        Types::TIME_IMMUTABLE,
    ];

    /** @var array<string, SourceTable> */
    private array $sourceTables = [];
    /** @var array<string, string> */
    private array $sourceStrings = [];
    /** @var array<string, TargetTable> */
    private array $targetTables = [];
    /** @var array<string, TargetTable> */
    private array $excludedTargetTables = [];
    /** @var array<string, string> */
    private array $excludedTargetColumnTypes = [];

    private int $targetDataFrameCuttingLength = 10;

    /**
     * @internal
     */
    public function __construct(private string $identifier)
    {
    }

    /**
     * @api
     */
    public static function create(string $identifier): TableDefinition
    {
        return new self($identifier);
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
    public function hasSourceTable(string $identifier): bool
    {
        return isset($this->sourceTables[$identifier]);
    }

    /**
     * @api
     */
    public function getSourceTable(string $identifier): SourceTable
    {
        if (!$this->hasSourceTable($identifier)) {
            throw new MissingSourceTableException(sprintf('missing source table "%s" for definition "%s"', $identifier, $this->identifier), 1621654993);
        }

        return $this->sourceTables[$identifier];
    }

    /**
     * @param array<int, string|SourceColumn> $columns
     *
     * @api
     */
    public function addSourceTable(SourceTable|string $table, array $columns = []): TableDefinition
    {
        $table = is_string($table) ? SourceTable::create($table) : $table;
        $table->addColumns($columns);
        $this->sourceTables[$table->getIdentifier()] = $table;

        return $this;
    }

    /**
     * @api
     */
    public function removeSourceTable(string $identifier): TableDefinition
    {
        unset($this->sourceTables[$identifier]);

        return $this;
    }

    /**
     * @return array<string, SourceTable>
     *
     * @api
     */
    public function getSourceTables(): array
    {
        return $this->sourceTables;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getSourceTableIdentifiers(): array
    {
        return array_keys($this->sourceTables);
    }

    /**
     * @api
     */
    public function hasTargetTable(string $identifier): bool
    {
        return isset($this->targetTables[$identifier]);
    }

    /**
     * @api
     */
    public function getTargetTable(string $identifier): TargetTable
    {
        if (!$this->hasTargetTable($identifier)) {
            throw new MissingTargetTableException(sprintf('missing target table "%s" for definition "%s"', $identifier, $this->identifier), 1621654994);
        }

        return $this->targetTables[$identifier];
    }

    /**
     * @param array<int, string|TargetColumn> $columns
     * @param array<int, string|TargetColumn> $excludeColumns
     * @param array<string, string>           $excludeColumnTypes
     *
     * @api
     */
    public function addTargetTable(TargetTable|string $table, array $columns = [], array $excludeColumns = [], array $excludeColumnTypes = []): TableDefinition
    {
        $table = is_string($table) ? TargetTable::create($table) : $table;
        $table->addColumns($columns);
        $table->excludeColumns($excludeColumns);
        $table->excludeColumnTypes($excludeColumnTypes);
        $this->targetTables[$table->getIdentifier()] = $table;

        return $this;
    }

    /**
     * @api
     */
    public function removeTargetTable(string $identifier): TableDefinition
    {
        unset($this->targetTables[$identifier]);

        return $this;
    }

    /**
     * @return array<string, TargetTable>
     *
     * @api
     */
    public function getTargetTables(): array
    {
        return $this->targetTables;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getTargetTableIdentifiers(): array
    {
        return array_keys($this->targetTables);
    }

    /**
     * @api
     */
    public function isTargetTableExcluded(string $identifier): bool
    {
        return isset($this->excludedTargetTables[$identifier]);
    }

    /**
     * @api
     */
    public function getExcludedTargetTable(string $identifier): TargetTable
    {
        if (!$this->isTargetTableExcluded($identifier)) {
            throw new MissingExcludedTableException(sprintf('missing excluded table "%s" for definition "%s"', $identifier, $this->identifier), 1621654995);
        }

        return $this->excludedTargetTables[$identifier];
    }

    /**
     * @param array<int, string|TargetTable> $tables
     *
     * @api
     */
    public function excludeTargetTables(array $tables): TableDefinition
    {
        foreach ($tables as $table) {
            $this->excludeTargetTable($table);
        }

        return $this;
    }

    /**
     * @api
     */
    public function excludeTargetTable(TargetTable|string $table): TableDefinition
    {
        $table = is_string($table) ? TargetTable::create($table) : $table;
        $this->excludedTargetTables[$table->getIdentifier()] = $table;

        return $this;
    }

    /**
     * @api
     */
    public function removeExcludedTargetTable(string $identifier): TableDefinition
    {
        unset($this->excludedTargetTables[$identifier]);

        return $this;
    }

    /**
     * @return array<string, TargetTable>
     *
     * @api
     */
    public function getExcludedTargetTables(): array
    {
        return $this->excludedTargetTables;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getExcludedTargetTableIdentifiers(): array
    {
        return array_keys($this->excludedTargetTables);
    }

    /**
     * @api
     */
    public function isTargetColumnTypeExcluded(string $columnType): bool
    {
        return isset($this->excludedTargetColumnTypes[$columnType]);
    }

    /**
     * @param array<int, string> $columnTypes
     *
     * @api
     */
    public function excludeTargetColumnTypes(array $columnTypes): TableDefinition
    {
        foreach ($columnTypes as $columnType) {
            $this->excludeTargetColumnType($columnType);
        }

        return $this;
    }

    /**
     * @api
     */
    public function excludeTargetColumnType(string $columnType): TableDefinition
    {
        $this->excludedTargetColumnTypes[$columnType] = $columnType;

        return $this;
    }

    /**
     * @api
     */
    public function removeExcludedTargetColumnType(string $columnType): TableDefinition
    {
        unset($this->excludedTargetColumnTypes[$columnType]);

        return $this;
    }

    /**
     * @return array<string, string>
     *
     * @api
     */
    public function getExcludedTargetColumnTypes(): array
    {
        return $this->excludedTargetColumnTypes;
    }

    /**
     * @api
     */
    public function hasSourceString(string $string): bool
    {
        return isset($this->sourceStrings[md5($string)]);
    }

    /**
     * @api
     */
    public function addSourceString(string $string): TableDefinition
    {
        $this->sourceStrings[md5($string)] = $string;

        return $this;
    }

    /**
     * @api
     */
    public function removeSourceString(string $string): TableDefinition
    {
        unset($this->sourceStrings[md5($string)]);

        return $this;
    }

    /**
     * @return array<string, string>
     *
     * @api
     */
    public function getSourceStrings(): array
    {
        return $this->sourceStrings;
    }

    /**
     * @internal
     */
    public function getTargetDataFrameCuttingLength(): int
    {
        return $this->targetDataFrameCuttingLength;
    }

    /**
     * @api
     */
    public function setTargetDataFrameCuttingLength(int $length): TableDefinition
    {
        $this->targetDataFrameCuttingLength = $length;

        return $this;
    }
}
