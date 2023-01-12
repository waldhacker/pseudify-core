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
class Finding
{
    /**
     * @param array<array-key, string> $targetDataFrames
     */
    public function __construct(
        private SourceTable $sourceTable,
        private SourceColumn $sourceColumn,
        private TargetTable $targetTable,
        private TargetColumn $targetColumn,
        private string $sourceData,
        private array $targetDataFrames
    ) {
    }

    public function getSourceTable(): SourceTable
    {
        return $this->sourceTable;
    }

    public function getSourceColumn(): SourceColumn
    {
        return $this->sourceColumn;
    }

    public function getTargetTable(): TargetTable
    {
        return $this->targetTable;
    }

    public function getTargetColumn(): TargetColumn
    {
        return $this->targetColumn;
    }

    /**
     * @internal
     */
    public function getSourceData(): string
    {
        return $this->sourceData;
    }

    /**
     * @return array<array-key, string>
     */
    public function getTargetDataFrames(): array
    {
        return $this->targetDataFrames;
    }

    /**
     * @return array{source: string, sourceData: string, target: string}
     */
    public function toArray(): array
    {
        return [
            'source' => sprintf(
                '%s.%s',
                $this->sourceTable->getIdentifier(),
                $this->sourceColumn->getIdentifier()
            ),
            'sourceData' => $this->getSourceData(),
            'target' => sprintf(
                '%s.%s',
                $this->targetTable->getIdentifier(),
                $this->targetColumn->getIdentifier()
            ),
        ];
    }

    public function __toString()
    {
        return sprintf(
            '%s.%s (%s) -> %s.%s',
            $this->sourceTable->getIdentifier(),
            $this->sourceColumn->getIdentifier(),
            $this->getSourceData(),
            $this->targetTable->getIdentifier(),
            $this->targetColumn->getIdentifier()
        );
    }
}
