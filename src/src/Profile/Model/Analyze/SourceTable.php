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

class SourceTable
{
    /** @var array<string, SourceColumn> */
    private array $columns = [];

    /**
     * @param array<int, string|SourceColumn> $columns
     *
     * @internal
     */
    public function __construct(private string $identifier, array $columns = [])
    {
        $this->addColumns($columns);
    }

    /**
     * @param array<int, string|SourceColumn> $columns
     *
     * @api
     */
    public static function create(string $identifier, array $columns = []): SourceTable
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
    public function getColumn(string $identifier): SourceColumn
    {
        if (!$this->hasColumn($identifier)) {
            throw new MissingSourceColumnException(sprintf('missing source column "%s" for table "%s"', $identifier, $this->identifier), 1621654996);
        }

        return $this->columns[$identifier];
    }

    /**
     * @param array<int, string|SourceColumn> $columns
     *
     * @api
     */
    public function addColumns(array $columns): SourceTable
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    /**
     * @api
     */
    public function addColumn(SourceColumn|string $column): SourceTable
    {
        $column = is_string($column) ? SourceColumn::create($column) : $column;
        $this->columns[$column->getIdentifier()] = $column;

        return $this;
    }

    /**
     * @api
     */
    public function removeColumn(string $identifier): SourceTable
    {
        unset($this->columns[$identifier]);

        return $this;
    }

    /**
     * @return array<string, SourceColumn>
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
