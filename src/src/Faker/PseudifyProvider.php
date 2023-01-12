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

namespace Waldhacker\Pseudify\Core\Faker;

use Faker\Provider\Internet;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PseudifyProvider extends Internet implements FakeDataProviderInterface
{
    /**
     * @api
     */
    public function bcryptPassword(): string
    {
        $password = $this->password();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
        $hashedPassword = empty($hashedPassword) ? '' : $hashedPassword;

        return $hashedPassword;
    }

    /**
     * @api
     */
    public function argon2iPassword(): string
    {
        $password = $this->password();
        $hashedPassword = password_hash($password, PASSWORD_ARGON2I, ['memory_cost' => 8, 'time_cost' => 1]);
        $hashedPassword = empty($hashedPassword) ? '' : $hashedPassword;

        return $hashedPassword;
    }

    /**
     * @api
     */
    public function argon2idPassword(): string
    {
        $password = $this->password();
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 8, 'time_cost' => 1]);
        $hashedPassword = empty($hashedPassword) ? '' : $hashedPassword;

        return $hashedPassword;
    }
}
