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

class TargetDataDecoderContext
{
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
        $this->rawData = $rawData;
        $this->decodedData = $decodedData;
        $this->datebaseRow = $datebaseRow;
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
     * @api
     */
    public function setDecodedData(mixed $decodedData): TargetDataDecoderContext
    {
        $this->decodedData = $decodedData;

        return $this;
    }

    /**
     * @internal
     */
    public function withDecodedData(mixed $decodedData): TargetDataDecoderContext
    {
        return new self($this->rawData, $decodedData, $this->datebaseRow);
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
}
