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

namespace Waldhacker\Pseudify\Core\Database;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @internal
 */
class ConnectionManager
{
    private ?string $connectionName = null;

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function setConnectionName(?string $connectionName): void
    {
        $this->connectionName = $connectionName;
    }

    public function getConnection(): Connection
    {
        return $this->doctrine->getConnection($this->connectionName);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->doctrine->getConnections();
    }
}
