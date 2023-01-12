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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\Schema;
use Waldhacker\Pseudify\Core\Processor\Encoder\ChainedEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\EncoderInterface;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileCollection;
use Waldhacker\Pseudify\Core\Profile\Analyze\TableDefinitionAutoConfiguration;

#[AsCommand(
    name: 'pseudify:debug:analyze',
    description: 'Show analyzation profile info',
)]
class DebugAnalyzeProfileCommand extends Command
{
    public function __construct(
        private ProfileCollection $profileCollection,
        private TableDefinitionAutoConfiguration $tableDefinitionAutoConfiguration,
        private ConnectionManager $connectionManager,
        private Schema $schema
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'The analyzation profile'
            )
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
        $io = new SymfonyStyle($input, $output);
        $this->initializeConnection($input);

        /** @var array<int, string|int>|string|null $profile */
        $profile = $input->getArgument('profile') ?? '';
        $profile = is_array($profile) ? (string) $profile[0] : (string) $profile;

        if (!$this->profileCollection->hasProfile($profile)) {
            throw new InvalidArgumentException(sprintf('invalid profile "%s". allowed profiles: "%s"', $profile, implode(',', $this->profileCollection->getProfileIdentifiers())), 1668974691);
        }

        $profile = $this->profileCollection->getProfile($profile);

        $tableDefinition = $this->tableDefinitionAutoConfiguration->configure($profile->getTableDefinition());

        $io->title(sprintf('Analyzer profile "%s"', $tableDefinition->getIdentifier()));

        $io->section('Basis configuration');

        $io->table(
            ['Key', 'Value'],
            [
                ['Shown characters before and after the finding', $tableDefinition->getTargetDataFrameCuttingLength()],
            ]
        );

        $io->section('Collect search data from this tables');

        $tableData = [];
        foreach ($tableDefinition->getSourceTables() as $table) {
            foreach ($table->getColumns() as $column) {
                $data = [
                    $table->getIdentifier(),
                    sprintf(
                        '%s (%s)',
                        $column->getIdentifier(),
                        $this->schema->getColumn($table->getIdentifier(), $column->getIdentifier())['column']->getType()->getName()
                    ),
                    implode('>', $this->buildEncoderList($column->getEncoder())),
                    implode('>', $column->getDataProcessingIdentifiers()),
                ];

                $tableData[] = $data;
            }
        }
        $io->table(['Table', 'column', 'data decoders', 'data collectors'], $tableData);

        if (!empty($tableDefinition->getSourceStrings())) {
            $io->section('Search for this strings');
            $io->table(['String'], array_map(fn (string $data): array => [$data], $tableDefinition->getSourceStrings()));
        }

        $io->section('Search data in this tables');

        $tableData = [];
        foreach ($tableDefinition->getTargetTables() as $table) {
            foreach ($table->getColumns() as $column) {
                $dataProcessingIdentifiers = $column->getDataProcessingIdentifiers();
                $data = [
                    $table->getIdentifier(),
                    sprintf(
                        '%s (%s)',
                        $column->getIdentifier(),
                        $this->schema->getColumn($table->getIdentifier(), $column->getIdentifier())['column']->getType()->getName()
                    ),
                    implode('>', $this->buildEncoderList($column->getEncoder())),
                    implode('>', empty($dataProcessingIdentifiers) ? ['no further processing'] : $dataProcessingIdentifiers),
                ];

                $tableData[] = $data;
            }
        }
        $io->table(['Table', 'column', 'data decoders', 'special data decoders'], $tableData);

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

    /**
     * @return array<int, scalar>
     */
    private function buildEncoderList(EncoderInterface $encoder): array
    {
        $encoders = [];
        if ($encoder instanceof ChainedEncoder) {
            foreach ($encoder->getEncoders() as $subEncoder) {
                $encoders = array_merge($encoders, $this->buildEncoderList($subEncoder));
            }
        } else {
            $shortName = (new \ReflectionClass($encoder))->getShortName();
            $convenientShortName = preg_replace('/(.*)Encoder$/', '$1', $shortName);
            $encoders[] = $convenientShortName ?: $shortName;
        }

        return $encoders;
    }
}
