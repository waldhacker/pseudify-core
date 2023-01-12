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

namespace Waldhacker\Pseudify\Core\Command;

use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\Schema;

#[AsCommand(
    name: 'pseudify:debug:table_schema',
    description: 'Show database schema info',
)]
class DebugTableSchemaInfoCommand extends Command
{
    public function __construct(
        private Schema $schema,
        private ConnectionManager $connectionManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'connection',
                null,
                InputOption::VALUE_REQUIRED,
                'The named database connection',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeConnection($input);
        $connection = $this->connectionManager->getConnection();

        $schema = array_map(
            fn (string $table): array => [
                'table' => $table,
                'columns' => $this->schema->listTableColumns($table),
            ],
            $this->schema->listTableNames()
        );

        usort(
            $schema,
            /**
             * @param array{table: string, columns: array} $itemA
             * @param array{table: string, columns: array} $itemB
             */
            static fn (array $itemA, array $itemB): int => strcmp($itemA['table'], $itemB['table'])
        );

        $io = new SymfonyStyle($input, $output);

        $platform = $connection->getDatabasePlatform();
        foreach ($schema as $data) {
            $io->section($data['table']);
            $tableData = [];
            foreach ($data['columns'] as $column) {
                $queryBuilder = $connection->createQueryBuilder();
                $queryBuilder->select($connection->quoteIdentifier($column['name']))
                  ->from($connection->quoteIdentifier($data['table']))
                  ->where($queryBuilder->expr()->isNotNull($connection->quoteIdentifier($column['name'])))
                  ->setMaxResults(1);

                if ($platform instanceof SQLServer2012Platform) {
                    $queryBuilder->orderBy(sprintf('DATALENGTH(%s)', $connection->quoteIdentifier($column['name'])), 'DESC')
                        ->addOrderBy(sprintf('CONVERT(VARCHAR(MAX), %s)', $connection->quoteIdentifier($column['name'])), 'DESC');
                } elseif ($platform instanceof PostgreSQL94Platform) {
                    $queryBuilder->orderBy(sprintf('LENGTH(CAST(%s AS TEXT))', $connection->quoteIdentifier($column['name'])), 'DESC')
                        ->addOrderBy($connection->quoteIdentifier($column['name']), 'DESC');
                } else {
                    $queryBuilder->orderBy(sprintf('LENGTH(%s)', $connection->quoteIdentifier($column['name'])), 'DESC')
                        ->addOrderBy($connection->quoteIdentifier($column['name']), 'DESC');
                }

                $row = $queryBuilder->executeQuery()->fetchAssociative();

                $exampleData = null;
                if (false !== $row) {
                    /** @var mixed $rowData */
                    $rowData = $row[$column['name']] ?? null;
                    if (is_resource($rowData)) {
                        $rowData = stream_get_contents($rowData);
                    }

                    $exampleData = null === $rowData ? '_NULL' : (is_string($rowData) ? $rowData : var_export($rowData, true));
                    $exampleData = strlen($exampleData) > 100 ? substr($exampleData, 0, 100).'...' : $exampleData;
                }

                $tableData[] = [$column['name'], $column['column']->getType()->getName(), $exampleData];
            }
            $io->table(['column', 'type', 'data example'], $tableData);
        }

        return Command::SUCCESS;
    }

    private function initializeConnection(InputInterface $input): void
    {
        if ($input->hasOption('connection')) {
            /** @var array<int, string|int>|string|null $connectionName */
            $connectionName = $input->getOption('connection') ?? null;
            $connectionName = is_array($connectionName) ? $connectionName[0] : $connectionName;
            $this->connectionManager->setConnectionName(is_string($connectionName) ? $connectionName : null);
        }
    }
}
