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

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileCollection as AnalyzeProfileCollection;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileCollection as PseudonymizeProfileCollection;

#[AsCommand(
    name: 'pseudify:information',
    description: 'Show application information',
)]
class InformationCommand extends Command
{
    public function __construct(
        private AnalyzeProfileCollection $analyzeProfileCollection,
        private PseudonymizeProfileCollection $pseudonymizeProfileCollection,
        private ConnectionManager $connectionManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Registered analyze profiles');
        $io->table(
            ['Profile name'],
            array_map(static fn (string $profileName): array => [$profileName], $this->analyzeProfileCollection->getProfileIdentifiers())
        );
        $io->section('Registered pseudonymize profiles');
        $io->table(
            ['Profile name'],
            array_map(static fn (string $profileName): array => [$profileName], $this->pseudonymizeProfileCollection->getProfileIdentifiers())
        );
        $io->section('Registered doctrine types');
        $io->table(
            ['Doctrine type name', 'Doctrine type implementation'],
            array_map(static fn (Type $type): array => [$type->getName(), get_class($type)], Type::getTypeRegistry()->getMap())
        );

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/configuration.html#driver
        $io->section('Available built-in database drivers');
        $io->table(
            ['Driver', 'Description', 'Installed version'],
            [
                [new TableCell('MySQL / MariaDB', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_mysql', 'A MySQL driver that uses the pdo_mysql PDO extension', empty(phpversion('pdo_mysql')) ? 'N/A' : phpversion('pdo_mysql')],
                ['mysqli', 'A MySQL driver that uses the mysqli extension', empty(phpversion('mysqli')) ? 'N/A' : phpversion('mysqli')],
                new TableSeparator(),
                [new TableCell('PostgreSQL', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_pgsql', 'A PostgreSQL driver that uses the pdo_pgsql PDO extension', empty(phpversion('pdo_pgsql')) ? 'N/A' : phpversion('pdo_pgsql')],
                new TableSeparator(),
                [new TableCell('SQLite', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_sqlite', 'An SQLite driver that uses the pdo_sqlite PDO extension', empty(phpversion('pdo_sqlite')) ? 'N/A' : phpversion('pdo_sqlite')],
                ['sqlite3', 'An SQLite driver that uses the sqlite3 extension', empty(phpversion('sqlite3')) ? 'N/A' : phpversion('sqlite3')],
                new TableSeparator(),
                [new TableCell('SQL Server', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_sqlsrv', 'A Microsoft SQL Server driver that uses pdo_sqlsrv PDO', empty(phpversion('pdo_sqlsrv')) ? 'N/A' : phpversion('pdo_sqlsrv')],
                ['sqlsrv', 'A Microsoft SQL Server driver that uses the sqlsrv PHP extension', empty(phpversion('sqlsrv')) ? 'N/A' : phpversion('sqlsrv')],
                new TableSeparator(),
                [new TableCell('Oracle Database', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_oci', 'An Oracle driver that uses the pdo_oci PDO extension (not recommended by doctrine)', empty(phpversion('pdo_oci')) ? 'N/A' : phpversion('pdo_oci')],
                ['oci8', 'An Oracle driver that uses the oci8 PHP extension', empty(phpversion('oci8')) ? 'N/A' : phpversion('oci8')],
                new TableSeparator(),
                [new TableCell('IBM DB2', ['colspan' => 3])],
                new TableSeparator(),
                ['pdo_ibm', 'An DB2 driver that uses the pdo_ibm PHP extension', empty(phpversion('pdo_ibm')) ? 'N/A' : phpversion('pdo_ibm')],
                ['ibm_db2', 'An DB2 driver that uses the ibm_db2 extension', empty(phpversion('ibm_db2')) ? 'N/A' : phpversion('ibm_db2')],
            ]
        );

        foreach ($this->connectionManager->getConnections() as $connectionName => $connection) {
            $platform = $connection->getDatabasePlatform();
            $configuredBuiltInDriver = str_starts_with(get_class($connection->getDriver()), 'Doctrine\\DBAL') ? ($connection->getParams()['driver'] ?? null) : null;
            /** @var array<string, string> $doctrineTypeMappings */
            $doctrineTypeMappings = (new \ReflectionClass($platform))->getProperty('doctrineTypeMapping')->getValue($platform);
            $doctrineTypeMappings = array_map(
                static fn (string $databaseType, string $doctrineTypeName): array => [$databaseType, $doctrineTypeName, get_class(Type::getType($doctrineTypeName))],
                array_keys($doctrineTypeMappings),
                array_values($doctrineTypeMappings),
            );

            $io->title(sprintf('Connection information for connection "%s"', $connectionName));

            $io->section('Registered doctrine database data type mappings');
            $io->table(
                ['Database type', 'Doctrine type name', 'Doctrine type implementation'],
                $doctrineTypeMappings
            );

            $io->section('Connection details');
            $io->table(
                ['Name', 'Value'],
                [
                    ['Used connection implementation', get_class($connection)],
                    ['Used database driver implementation', get_class($connection->getDriver())],
                    ['Used database platform implementation', get_class($platform)],
                    ['Used database platform version', (new \ReflectionMethod($connection, 'getDatabasePlatformVersion'))->invoke($connection) ?? 'N/A'],
                    ['Used built-in database driver', $configuredBuiltInDriver ? sprintf('%s (%s)', $configuredBuiltInDriver, empty(phpversion($configuredBuiltInDriver)) ? 'N/A' : phpversion($configuredBuiltInDriver)) : 'N/A'],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
