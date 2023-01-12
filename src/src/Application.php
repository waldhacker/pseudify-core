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

namespace Waldhacker\Pseudify\Core;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    private array $commandIncludeList = [
        'pseudify:analyze',
        'pseudify:debug:analyze',
        'pseudify:debug:pseudonymize',
        'pseudify:debug:table_schema',
        'pseudify:information',
        'pseudify:pseudonymize',
        'cache:clear',
        'config:dump-reference',
        'debug:config',
        'debug:container',
        'debug:autowiring',
        'debug:dotenv',
        'debug:event-dispatcher',
    ];

    public function add(Command $command): ?Command
    {
        $this->registerCommands();

        if ('prod' === $this->getKernel()->getEnvironment() && !in_array($command->getName(), $this->commandIncludeList, true)) {
            $command->setHidden();
        }

        return parent::add($command);
    }
}
