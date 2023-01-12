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

use Symfony\Component\String\Exception\InvalidArgumentException;
use Symfony\Component\String\UnicodeString;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

class SourceDataCollectorPreset
{
    /**
     * @api
     */
    public static function scalarData(?string $processingIdentifier = null, int $minimumGraphemeLength = 3): DataProcessingInterface
    {
        return new DataProcessing(
            static function (SourceDataCollectorContext $context) use ($minimumGraphemeLength): void {
                /** @var mixed $decodedData */
                $decodedData = $context->getDecodedData();
                if (is_string($decodedData)) {
                    $graphemeLength = self::normalizeString($decodedData)->length();
                    if ($graphemeLength < $minimumGraphemeLength) {
                        return;
                    }
                }

                $context->addCollectedData($decodedData);
            },
            $processingIdentifier
        );
    }

    private static function normalizeString(string $input): UnicodeString
    {
        try {
            $result = new UnicodeString($input);
        } catch (InvalidArgumentException $e) {
            $normalized = preg_replace('/[^[:print:]]/', '', $input);
            $result = new UnicodeString($normalized ?? $input);
        }

        return $result;
    }
}
