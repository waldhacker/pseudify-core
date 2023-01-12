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
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Processor\PseudonymizeProcessor;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileCollection;

#[AsCommand(
    name: 'pseudify:pseudonymize',
    description: 'Pseudonymize the database',
)]
class PseudonymizeCommand extends Command
{
    public function __construct(
        private ProfileCollection $profileCollection,
        private PseudonymizeProcessor $processor,
        private ConnectionManager $connectionManager,
        private TagAwareCacheInterface $cache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'The pseudonymization profile'
            )
            ->addOption(
                'connection',
                null,
                InputOption::VALUE_REQUIRED,
                'The named database connection',
                null
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'show update queries while not executing it'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeConnection($input);
        $this->cache->invalidateTags(['pseudonymize_fakedata']);

        /** @var array<int, string|int>|string|null $profile */
        $profile = $input->getArgument('profile') ?? '';
        $profile = is_array($profile) ? (string) $profile[0] : (string) $profile;

        if (!$this->profileCollection->hasProfile($profile)) {
            throw new InvalidArgumentException(sprintf('invalid profile "%s". allowed profiles: "%s"', $profile, implode(',', $this->profileCollection->getProfileIdentifiers())), 1619592554);
        }

        /** @var array<int, string>|string|null $dryRun */
        $dryRun = $input->getOption('dry-run');
        $dryRun = is_array($dryRun) ? (bool) $dryRun[0] : (bool) $dryRun;

        $profile = $this->profileCollection->getProfile($profile);
        $this->processor->setIo(new SymfonyStyle($input, $output))->process($profile, $dryRun);
        $this->cache->invalidateTags(['pseudonymize_fakedata']);

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
