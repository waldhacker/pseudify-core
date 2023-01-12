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

/**
 * @internal
 */
class Stats
{
    /** @var array<array-key, int> */
    private array $sourceTableRowCount;
    /** @var array<array-key, int> */
    private array $sourceTableColumnCount;
    /** @var array<array-key, int> */
    private array $targetTableRowCount;
    /** @var array<array-key, int> */
    private array $targetTableColumnCount;

    /*
     * @param array<string, mixed> $sourceTableRowCount
     * @param array<string, mixed> $sourceTableColumnCount
     * @param array<string, mixed> $targetTableRowCount
     * @param array<string, mixed> $targetTableColumnCount
     */
    public function __construct(
        array $sourceTableRowCount,
        array $sourceTableColumnCount,
        array $targetTableRowCount,
        array $targetTableColumnCount,
        private bool $withCustomStringSearch
    ) {
        $this->sourceTableRowCount = array_filter($sourceTableRowCount, 'is_int');
        $this->sourceTableColumnCount = array_filter($sourceTableColumnCount, 'is_int');
        $this->targetTableRowCount = array_filter($targetTableRowCount, 'is_int');
        $this->targetTableColumnCount = array_filter($targetTableColumnCount, 'is_int');
    }

    public function getTotalProcessings(): int
    {
        $totalTargetProcessings = $this->getTotalTargetProcessings();
        $processings = $this->withCustomStringSearch ? $totalTargetProcessings : 0;
        foreach ($this->sourceTableRowCount as $identifier => $rowCount) {
            $sourceProcessings = $rowCount * ($this->sourceTableColumnCount[$identifier] ?? 0);
            $processings += $sourceProcessings * $totalTargetProcessings;
        }

        return $processings;
    }

    public function getTotalTargetProcessings(): int
    {
        $processings = 0;
        foreach ($this->targetTableRowCount as $identifier => $rowCount) {
            $processings += $rowCount * ($this->targetTableColumnCount[$identifier] ?? 0);
        }

        return $processings;
    }

    public function getSourceTableRowCount(string $identifier): int
    {
        return $this->sourceTableRowCount[$identifier] ?? 0;
    }

    public function getSourceTableColumnCount(string $identifier): int
    {
        return $this->sourceTableColumnCount[$identifier] ?? 0;
    }

    public function getTargetTableRowCount(string $identifier): int
    {
        return $this->targetTableRowCount[$identifier] ?? 0;
    }

    public function getTargetTableColumnCount(string $identifier): int
    {
        return $this->targetTableColumnCount[$identifier] ?? 0;
    }
}
