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

namespace Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize;

use Waldhacker\Pseudify\Core\Faker\Faker;

class DataManipulatorContext
{
    /**
     * @param array<array-key, mixed> $datebaseRow
     *
     * @internal
     */
    public function __construct(
        private Faker $faker,
        private mixed $rawData,
        private mixed $decodedData,
        private array $datebaseRow,
        private mixed $processedData = null
    ) {
    }

    /**
     * @api
     */
    public function fake(string $scope = Faker::DEFAULT_SCOPE, mixed $source = null): Faker
    {
        return $this->faker
            ->withScope($scope)
            ->withSource($source ? $source : $this->getProcessedData());
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
     * @api
     */
    public function getProcessedData(): mixed
    {
        return $this->processedData;
    }

    /**
     * @api
     */
    public function setProcessedData(mixed $processedData): DataManipulatorContext
    {
        $this->processedData = $processedData;

        return $this;
    }

    /**
     * @internal
     */
    public function withProcessedData(mixed $processedData): DataManipulatorContext
    {
        return new self($this->faker, $this->rawData, $this->decodedData, $this->datebaseRow, $processedData);
    }
}
