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

namespace Waldhacker\Pseudify\Core\Processor\Processing\Analyze;

class SourceDataCollectorContext
{
    /** @var array<array-key, mixed> */
    private array $collectedData = [];

    /**
     * @param array<array-key, mixed> $datebaseRow
     *
     * @internal
     */
    public function __construct(
        private mixed $rawData,
        private mixed $decodedData,
        private array $datebaseRow
    ) {
    }

    /**
     * @api
     */
    public function getRawData(): mixed
    {
        return $this->rawData;
    }

    /**
     * @api
     */
    public function getDecodedData(): mixed
    {
        return $this->decodedData;
    }

    /**
     * @return array<array-key, mixed>
     *
     * @api
     */
    public function getDatebaseRow(): array
    {
        return $this->datebaseRow;
    }

    /**
     * @return array<array-key, mixed>
     *
     * @api
     */
    public function getCollectedData(): array
    {
        return $this->collectedData;
    }

    /**
     * @api
     */
    public function addCollectedData(mixed $data): SourceDataCollectorContext
    {
        $this->collectedData[md5(serialize($data))] = $data;

        return $this;
    }

    /**
     * @api
     */
    public function removeCollectedData(string $identifier): SourceDataCollectorContext
    {
        unset($this->collectedData[$identifier]);

        return $this;
    }
}
