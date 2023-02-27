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

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Symfony\Component\Console\Style\SymfonyStyle;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\Schema;
use Waldhacker\Pseudify\Core\Faker\Faker;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulator;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\TableDefinitionAutoConfiguration;

/**
 * @internal
 */
class PseudonymizeProcessor
{
    /** @var array<string, array<string, DoctrineColumn>> */
    private array $columnInfo = [];

    public function __construct(
        private TableDefinitionAutoConfiguration $tableDefinitionAutoConfiguration,
        private ConnectionManager $connectionManager,
        private Schema $schema,
        private DataManipulator $dataManipulator,
        private Faker $faker,
        private ?SymfonyStyle $io = null
    ) {
    }

    public function setIo(SymfonyStyle $io): PseudonymizeProcessor
    {
        $this->io = $io;

        return $this;
    }

    public function process(ProfileInterface $profile, bool $dryRun): ProfileInterface
    {
        $tableDefinition = $this->tableDefinitionAutoConfiguration->configure($profile->getTableDefinition());
        $connection = $this->connectionManager->getConnection();
        try {
            $connection->beginTransaction();

            foreach ($tableDefinition->getTables() as $table) {
                foreach ($table->getColumns() as $column) {
                    $this->setupColumnInfo($table, $column);
                }
            }

            foreach ($tableDefinition->getTables() as $table) {
                $result = $this->queryData($table);
                while ($row = $result->fetchAssociative()) {
                    foreach ($table->getColumns() as $column) {
                        /** @var mixed $originalData */
                        $originalData = $row[$column->getIdentifier()] ?? null;
                        if (is_resource($originalData)) {
                            $originalData = stream_get_contents($originalData);
                            $row[$column->getIdentifier()] = $originalData;
                        }

                        /** @var mixed $processedData */
                        $processedData = $this->processData($column, $row);

                        if ($originalData === $processedData) {
                            continue;
                        }

                        $updatedRows = $this->updateData($table, $column, $originalData, $processedData, $row, $dryRun);
                        /*
                        if (!$dryRun && ($this->io && 0 === $updatedRows)) {
                            $this->io->warning(sprintf('table "%s" column "%s" could not be updated!', $table->getIdentifier(), $column->getIdentifier()));
                        }
                        */
                    }
                }
            }

            $connection->commit();
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        if (!$dryRun && $this->io) {
            $this->io->writeln('done');
        }
        // @codeCoverageIgnoreEnd

        return $profile;
    }

    private function queryData(Table $table): Result
    {
        $connection = $this->connectionManager->getConnection();
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->select('*')->from($connection->quoteIdentifier($table->getIdentifier()));

        return $queryBuilder->executeQuery();
    }

    private function processData(Column $column, array $row): mixed
    {
        /** @var mixed $originalData */
        $originalData = $row[$column->getIdentifier()] ?? null;
        if (empty($originalData)) {
            return $originalData;
        }

        /** @var mixed $decodedData */
        $decodedData = $column->getEncoder()->decode($originalData);

        $context = new DataManipulatorContext($this->faker, $originalData, $decodedData, $row);
        /** @var mixed $processedData */
        $processedData = $this->dataManipulator->process($context, ...$column->getDataProcessings());

        return (string) $column->getEncoder()->encode($processedData);
    }

    private function updateData(Table $table, Column $column, mixed $originalData, mixed $processedData, array $row, bool $dryRun): int
    {
        $connection = $this->connectionManager->getConnection();
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->update($connection->quoteIdentifier($table->getIdentifier()))
            ->set(
                $connection->quoteIdentifier($column->getIdentifier()),
                $queryBuilder->createNamedParameter($processedData, $this->getBindingType($table, $column))
            )
            ->where(
                $queryBuilder->expr()->eq(
                    $connection->quoteIdentifier($column->getIdentifier()),
                    $queryBuilder->createNamedParameter($originalData, $this->getBindingType($table, $column))
                )
            );

        call_user_func(
            $column->getBeforeUpdateDataCallback(),
            $queryBuilder,
            $table,
            $column,
            $this->columnInfo[$table->getIdentifier()][$column->getIdentifier()],
            $originalData,
            $processedData,
            $row
        );

        if (true === $dryRun) {
            $this->dumpSql($queryBuilder);

            return 0;
        }

        return $queryBuilder->executeStatement();
    }

    private function getBindingType(Table $table, Column $column): int
    {
        $columnInfo = $this->columnInfo[$table->getIdentifier()][$column->getIdentifier()];

        return $column->getBindingType() ?? $columnInfo->getType()->getBindingType();
    }

    private function setupColumnInfo(Table $table, Column $column): void
    {
        $this->columnInfo[$table->getIdentifier()] = $this->columnInfo[$table->getIdentifier()] ?? [];
        if (null === ($this->columnInfo[$table->getIdentifier()][$column->getIdentifier()] ?? null)) {
            $columnInfo = $this->schema->getColumn($table->getIdentifier(), $column->getIdentifier());
            $this->columnInfo[$table->getIdentifier()][$column->getIdentifier()] = $columnInfo['column'];
        }
    }

    private function dumpSql(QueryBuilder $queryBuilder): void
    {
        $sql = $queryBuilder->getSQL();

        /** @var mixed $parameterValue */
        foreach ($queryBuilder->getParameters() as $parameterName => $parameterValue) {
            $sql = str_replace(
                sprintf(':%s', (string) $parameterName),
                sprintf(':%s:%s', $parameterName, var_export($parameterValue, true)),
                $sql
            );
        }

        if (null !== $this->io) {
            $this->io->text($sql);
        }
    }
}
