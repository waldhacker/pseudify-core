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

class Table
{
    /** @var array<string, Column> */
    private array $columns = [];

    /**
     * @param array<int, string|Column> $columns
     *
     * @internal
     */
    public function __construct(private string $identifier, array $columns = [])
    {
        $this->addColumns($columns);
    }

    /**
     * @param array<int, string|Column> $columns
     *
     * @api
     */
    public static function create(string $identifier, array $columns = []): Table
    {
        return new self($identifier, $columns);
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
    public function getColumn(string $identifier): Column
    {
        if (!$this->hasColumn($identifier)) {
            throw new MissingColumnException(sprintf('missing column "%s" for table "%s"', $identifier, $this->identifier), 1621654990);
        }

        return $this->columns[$identifier];
    }

    /**
     * @param array<int, string|Column> $columns
     *
     * @api
     */
    public function addColumns(array $columns): Table
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    /**
     * @api
     */
    public function addColumn(Column|string $column): Table
    {
        $column = is_string($column) ? Column::create($column) : $column;
        $this->columns[$column->getIdentifier()] = $column;

        return $this;
    }

    /**
     * @api
     */
    public function removeColumn(string $identifier): Table
    {
        unset($this->columns[$identifier]);

        return $this;
    }

    /**
     * @return array<string, Column>
     *
     * @api
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getColumnIdentifiers(): array
    {
        return array_keys($this->columns);
    }
}
