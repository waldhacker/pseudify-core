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

namespace Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize;

class TableDefinition
{
    /** @var array<string, Table> */
    private array $tables = [];

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
    public function hasTable(string $identifier): bool
    {
        return isset($this->tables[$identifier]);
    }

    /**
     * @api
     */
    public function getTable(string $identifier): Table
    {
        if (!$this->hasTable($identifier)) {
            throw new MissingTableException(sprintf('missing table "%s" for definition "%s"', $identifier, $this->identifier), 1621654991);
        }

        return $this->tables[$identifier];
    }

    /**
     * @param array<int, string|Column> $columns
     *
     * @api
     */
    public function addTable(Table|string $table, array $columns = []): TableDefinition
    {
        $table = is_string($table) ? Table::create($table) : $table;
        $table->addColumns($columns);
        $this->tables[$table->getIdentifier()] = $table;

        return $this;
    }

    /**
     * @api
     */
    public function removeTable(string $identifier): TableDefinition
    {
        unset($this->tables[$identifier]);

        return $this;
    }

    /**
     * @return array<string, Table>
     *
     * @api
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getTableIdentifiers(): array
    {
        return array_keys($this->tables);
    }
}
