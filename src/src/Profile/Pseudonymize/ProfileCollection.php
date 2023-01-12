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

namespace Waldhacker\Pseudify\Core\Profile\Pseudonymize;

class ProfileCollection
{
    /** @var array<string, ProfileInterface> */
    private array $profiles = [];

    /**
     * @internal
     */
    public function __construct(iterable $profiles = [])
    {
        foreach ($profiles as $profile) {
            if (!$profile instanceof ProfileInterface) {
                continue;
            }
            $this->profiles[$profile->getIdentifier()] = $profile;
        }
    }

    /**
     * @api
     */
    public function hasProfile(string $identifier): bool
    {
        return isset($this->profiles[$identifier]);
    }

    /**
     * @api
     */
    public function getProfile(string $identifier): ProfileInterface
    {
        if (!$this->hasProfile($identifier)) {
            throw new MissingProfileException(sprintf('missing profile "%s"', $identifier), 1621656965);
        }

        return $this->profiles[$identifier];
    }

    /**
     * @api
     */
    public function addProfile(ProfileInterface $profile): ProfileCollection
    {
        $this->profiles[$profile->getIdentifier()] = $profile;

        return $this;
    }

    /**
     * @api
     */
    public function removeProfile(string $identifier): ProfileCollection
    {
        unset($this->profiles[$identifier]);

        return $this;
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getProfileIdentifiers(): array
    {
        return array_keys($this->profiles);
    }
}
