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

namespace Waldhacker\Pseudify\Core\Processor;

use Doctrine\DBAL\Result;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Waldhacker\Pseudify\Core\Configuration\Configuration;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\Query;
use Waldhacker\Pseudify\Core\Processor\Analyze\DataSetComparator;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollector;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoder;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderContext;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileInterface;
use Waldhacker\Pseudify\Core\Profile\Analyze\Statistics;
use Waldhacker\Pseudify\Core\Profile\Analyze\TableDefinitionAutoConfiguration;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Finding;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Stats;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

/**
 * @internal
 */
class AnalyzeProcessor
{
    private ?ProgressBar $progressBar;

    public function __construct(
        private ConnectionManager $connectionManager,
        private SourceDataCollector $sourceDataCollector,
        private TargetDataDecoder $targetDataDecoder,
        private TableDefinitionAutoConfiguration $tableDefinitionAutoConfiguration,
        private Statistics $statistics,
        private Query $query,
        private DataSetComparator $dataSetComparator,
        private TagAwareCacheInterface $cache,
        private Configuration $configuration,
        private ?SymfonyStyle $io = null
    ) {
        $this->progressBar = null === $this->io ? null : $this->io->createProgressBar();
    }

    public function setIo(SymfonyStyle $io): AnalyzeProcessor
    {
        $this->io = $io;
        $this->progressBar = $io->createProgressBar();

        return $this;
    }

    public function process(ProfileInterface $profile): ProfileInterface
    {
        $tableDefinition = $this->tableDefinitionAutoConfiguration->configure($profile->getTableDefinition());

        $stats = $this->statistics->create($tableDefinition);
        $this->initializeProgressBar($stats);

        $findings = [];
        $totalTargetProcessings = $stats->getTotalTargetProcessings();

        foreach ($tableDefinition->getSourceTables() as $sourceTable) {
            $sourceResult = $this->querySourceData($sourceTable);
            while ($sourceRow = $sourceResult->fetchAssociative()) {
                foreach ($sourceTable->getColumns() as $sourceColumn) {
                    $collectedSourceData = $this->collectSourceData($sourceColumn, $sourceRow);
                    if (empty($collectedSourceData)) {
                        continue;
                    }

                    $findings = $this->findInTargetTables(
                        $tableDefinition,
                        $sourceTable,
                        $sourceColumn,
                        $collectedSourceData,
                        $findings
                    );
                }

                if ($this->progressBar) {
                    $this->progressBar->advance(
                        $stats->getSourceTableColumnCount($sourceTable->getIdentifier()) * $totalTargetProcessings
                    );
                }
            }
        }

        if (!empty($tableDefinition->getSourceStrings())) {
            $findings = $this->findCustomSourceStringsInTargetTables($tableDefinition, $findings);
            if ($this->progressBar) {
                $this->progressBar->advance($totalTargetProcessings);
            }
        }

        if ($this->progressBar) {
            $this->progressBar->finish();
        }

        $this->dumpFindings($findings);

        return $profile;
    }

    /**
     * @param array<string, Finding> $findings
     *
     * @return array<string, Finding>
     */
    private function findCustomSourceStringsInTargetTables(TableDefinition $tableDefinition, array $findings): array
    {
        return $this->findInTargetTables(
            $tableDefinition,
            new SourceTable('__custom__'),
            new SourceColumn('__custom__'),
            $tableDefinition->getSourceStrings(),
            $findings
        );
    }

    /**
     * @param array<string, Finding> $findings
     *
     * @return array<string, Finding>
     */
    private function findInTargetTables(
        TableDefinition $tableDefinition,
        SourceTable $sourceTable,
        SourceColumn $sourceColumn,
        array $collectedSourceData,
        array $findings
    ): array {
        foreach ($tableDefinition->getTargetTables() as $targetTable) {
            $targetResult = $this->queryTargetData($targetTable);
            while ($targetRow = $targetResult->fetchAssociative()) {
                foreach ($targetTable->getColumns() as $targetColumn) {
                    $collectedTargetData = $this->collectTargetData($targetColumn, $targetRow);
                    if (empty($collectedTargetData)) {
                        continue;
                    }

                    $findings = $this->compareDataSets(
                        $collectedSourceData,
                        $collectedTargetData,
                        $sourceTable,
                        $sourceColumn,
                        $targetTable,
                        $targetColumn,
                        $findings,
                        $tableDefinition->getTargetDataFrameCuttingLength()
                    );
                }
            }
        }

        return $findings;
    }

    /**
     * @param array<string, Finding> $findings
     *
     * @return array<string, Finding>
     */
    private function compareDataSets(
        array $collectedSourceData,
        array $collectedTargetData,
        SourceTable $sourceTable,
        SourceColumn $sourceColumn,
        TargetTable $targetTable,
        TargetColumn $targetColumn,
        array $findings,
        int $targetDataFrameCuttingLength
    ): array {
        $isVerbose = $this->io && ($this->io->isVerbose() || $this->io->isVeryVerbose());

        $currentFindings = $this->dataSetComparator->compareDataSets(
            $collectedSourceData,
            $collectedTargetData,
            $sourceTable,
            $sourceColumn,
            $targetTable,
            $targetColumn,
            $isVerbose,
            $targetDataFrameCuttingLength
        );

        $findings = array_merge($findings, $currentFindings);
        if ($isVerbose) {
            $this->dumpFindingsWithTargetDataFrame($currentFindings);
        }

        return $findings;
    }

    private function querySourceData(SourceTable $sourceTable): Result
    {
        $connection = $this->connectionManager->getConnection();
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            // select all values to be accessible within the data processors
            ->select('*')
            ->from($connection->quoteIdentifier($sourceTable->getIdentifier()));

        return $queryBuilder->executeQuery();
    }

    private function queryTargetData(TargetTable $targetTable): Result
    {
        $connection = $this->connectionManager->getConnection();
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select(...$this->query->quoteIdentifiersForSelect($targetTable->getColumnIdentifiers()))
            ->from($connection->quoteIdentifier($targetTable->getIdentifier()));

        return $queryBuilder->executeQuery();
    }

    private function collectSourceData(SourceColumn $column, array $row): array
    {
        /** @var mixed $originalData */
        $originalData = $row[$column->getIdentifier()] ?? null;
        if (empty($originalData)) {
            return [];
        }

        if (is_resource($originalData)) {
            $originalData = stream_get_contents($originalData);
        }

        /** @var mixed $decodedData */
        $decodedData = $column->getEncoder()->decode($originalData);

        $context = new SourceDataCollectorContext($originalData, $decodedData, $row);
        $collectedData = $this->sourceDataCollector->process($context, ...$column->getDataProcessings());

        return array_filter(array_unique($collectedData));
    }

    private function collectTargetData(TargetColumn $column, array $row): array
    {
        /** @var mixed $originalData */
        $originalData = $row[$column->getIdentifier()] ?? null;
        if (empty($originalData)) {
            return [];
        }

        if (is_resource($originalData)) {
            $originalData = stream_get_contents($originalData);
        }

        /** @var mixed $decodedData */
        $decodedData = $column->getEncoder()->decode($originalData);

        $context = new TargetDataDecoderContext($originalData, $decodedData, $row);
        $collectedData = $this->targetDataDecoder->process($context, ...$column->getDataProcessings());

        return array_filter(array_unique($collectedData));
    }

    /**
     * @param Finding[] $findings
     */
    private function dumpFindingsWithTargetDataFrame(array $findings): void
    {
        if (empty($findings)) {
            return;
        }

        if ($this->progressBar) {
            $this->progressBar->clear();
        }

        foreach ($findings as $finding) {
            foreach ($finding->getTargetDataFrames() as $targetDataFrame) {
                // Show same collected data frames only once (between different rows).
                // This is only convinience for the view, not a speed optimisation.
                $cacheKey = hash('md5', json_encode([
                    $finding->getSourceTable()->getIdentifier(),
                    $finding->getSourceColumn()->getIdentifier(),
                    $finding->getTargetTable()->getIdentifier(),
                    $finding->getTargetColumn()->getIdentifier(),
                    $finding->getSourceData(),
                    $targetDataFrame,
                    $this->configuration->getSecret(),
                ], JSON_THROW_ON_ERROR));

                $this->cache->get($cacheKey, function (ItemInterface $item) use ($finding, $targetDataFrame) {
                    $item->tag(['analyze_output']);
                    if ($this->io) {
                        $this->io->writeln(sprintf(
                            '%s.%s (%s) -> %s.%s (%s)',
                            $finding->getSourceTable()->getIdentifier(),
                            $finding->getSourceColumn()->getIdentifier(),
                            sprintf('<fg=#c0392b>%s</>', $finding->getSourceData()),
                            $finding->getTargetTable()->getIdentifier(),
                            $finding->getTargetColumn()->getIdentifier(),
                            str_replace($finding->getSourceData(), sprintf('<fg=#c0392b>%s</>', $finding->getSourceData()), $targetDataFrame)
                        ));
                    }
                });
            }
        }

        if ($this->progressBar) {
            $this->progressBar->display();
        }
    }

    /**
     * @param Finding[] $findings
     */
    private function dumpFindings(array $findings): void
    {
        if ($this->io) {
            $this->io->newLine();
            $this->io->title('summary');

            $tableRows = array_map(
                static fn (Finding $finding): array => $finding->toArray(),
                $findings
            );

            usort(
                $tableRows,
                /**
                 * @param array{source: string, sourceData: string, target: string} $itemA
                 * @param array{source: string, sourceData: string, target: string} $itemB
                 */
                static function (array $itemA, array $itemB): int {
                    $result = strcmp($itemA['source'], $itemB['source']);
                    if (0 === $result) {
                        $result = strcmp($itemA['target'], $itemB['target']);
                    }
                    if (0 === $result) {
                        $result = strcmp($itemA['sourceData'], $itemB['sourceData']);
                    }

                    return $result;
                }
            );

            if (empty($tableRows)) {
                $this->io->writeln('no data found');
            } else {
                $this->io->table(
                    ['source', 'data', 'seems to be in'],
                    $tableRows
                );
            }
        }
    }

    private function initializeProgressBar(Stats $stats): void
    {
        if ($this->progressBar) {
            $this->progressBar->setFormat('debug');
            $this->progressBar->setMaxSteps($stats->getTotalProcessings());
            $this->progressBar->start();
        }
    }
}
