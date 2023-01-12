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

namespace Waldhacker\Pseudify\Core\Processor\Analyze;

use Symfony\Component\String\Exception\InvalidArgumentException;
use Symfony\Component\String\UnicodeString;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Finding;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

class DataSetComparator
{
    /**
     * @param array<array-key, mixed> $collectedSourceData
     * @param array<array-key, mixed> $collectedTargetData
     *
     * @return array<string, Finding>
     */
    public function compareDataSets(
        array $collectedSourceData,
        array $collectedTargetData,
        SourceTable $sourceTable,
        SourceColumn $sourceColumn,
        TargetTable $targetTable,
        TargetColumn $targetColumn,
        bool $withTargetDataFrames,
        int $targetDataFrameCuttingLength = 10
    ): array {
        $normalizedSourceData = $this->normalizeStrings($collectedSourceData);
        $normalizedTargetData = $this->normalizeStrings($collectedTargetData);

        $findings = [];
        foreach ($normalizedTargetData as $targetData) {
            foreach ($normalizedSourceData as $sourceData) {
                if (
                    '__custom__' === $sourceTable->getIdentifier()
                    && '__custom__' === $sourceColumn->getIdentifier()
                    && $sourceData->startsWith('regex:')
                ) {
                    $sourceDataRegex = $sourceData->trimStart('regex:');
                    $sourceDataRegex = $sourceDataRegex->replaceMatches('/(?<!\\\\)#/', '\\#');

                    $matchedTargetDataItems = $targetData->match(sprintf('#%s#', (string) $sourceDataRegex), \PREG_SET_ORDER);
                    $sourceDataCollection = $this->normalizeMatches($matchedTargetDataItems);
                } else {
                    $sourceDataCollection = [$sourceData];
                }

                foreach ($sourceDataCollection as $sourceDataItem) {
                    $sourceDataString = (string) $sourceDataItem;

                    if (!$targetData->containsAny($sourceDataString)) {
                        continue;
                    }

                    $targetDataFrames = [];
                    if (true === $withTargetDataFrames) {
                        $targetDataFrames = $targetDataFrameCuttingLength > 0
                                            ? $this->extractTargetDataFrames($sourceDataItem, $targetData, $targetDataFrameCuttingLength)
                                            : [(string) $targetData];
                    }

                    $finding = new Finding(
                        $sourceTable,
                        $sourceColumn,
                        $targetTable,
                        $targetColumn,
                        $sourceDataString,
                        $targetDataFrames
                    );

                    $findings[(string) $finding] = $finding;
                }
            }
        }

        return $findings;
    }

    /**
     * @param array<array-key, mixed> $regexMatches
     *
     * @return UnicodeString[]
     */
    private function normalizeMatches(array $regexMatches): array
    {
        if (empty($regexMatches)) {
            return [];
        }

        $flatMatches = [];
        /** @var array<array-key, mixed> $matches */
        foreach ($regexMatches as $matches) {
            $flatMatches = array_merge($flatMatches, $matches);
        }
        $flatMatches = array_unique($flatMatches);

        $result = [];
        /** @var mixed $match */
        foreach ($flatMatches as $match) {
            $result[] = $this->normalizeString($match);
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $input
     *
     * @return UnicodeString[]
     */
    private function normalizeStrings(array $input): array
    {
        $result = [];
        /** @var mixed $data */
        foreach ($input as $data) {
            $result[] = $this->normalizeString($data);
        }

        return $result;
    }

    private function normalizeString(mixed $input): UnicodeString
    {
        if (is_string($input)) {
            try {
                $result = new UnicodeString($input);
            } catch (InvalidArgumentException $e) {
                $normalized = preg_replace('/[^[:print:]]/', '', $input);
                $result = new UnicodeString($normalized ?? $input);
            }
        } else {
            $result = new UnicodeString(var_export($input, true));
        }

        return $result;
    }

    /**
     * @return array<array-key, string>
     */
    private function extractTargetDataFrames(
        UnicodeString $sourceData,
        UnicodeString $targetData,
        int $targetDataFrameCuttingLength
    ): array {
        $targetDataFrames = [];
        $sourceData = (string) $sourceData;
        while ($targetData->containsAny($sourceData)) {
            $beforeString = $targetData->before($sourceData);
            $targetData = $targetData->after($sourceData);

            $startFrame = $beforeString->slice(0 - $targetDataFrameCuttingLength);
            $endFrame = $targetData->slice(0, $targetDataFrameCuttingLength);

            $frameString = sprintf(
                '%s%s%s',
                ($startFrame->isEmpty() || $beforeString->equalsTo((string) $startFrame) ? '' : '...').$startFrame,
                $sourceData,
                $endFrame.($endFrame->isEmpty() || $targetData->equalsTo((string) $endFrame) ? '' : '...')
            );

            $targetDataFrames[] = $frameString;
        }

        return $targetDataFrames;
    }
}
