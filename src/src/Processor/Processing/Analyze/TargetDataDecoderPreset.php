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

use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Waldhacker\Pseudify\Core\Processor\Encoder\JsonEncoder;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

class TargetDataDecoderPreset
{
    /**
     * @api
     */
    public static function normalizedJsonString(?string $processingIdentifier = null): DataProcessingInterface
    {
        return new DataProcessing(
            static function (TargetDataDecoderContext $context): void {
                /** @var mixed $rawData */
                $rawData = $context->getRawData();
                if (!is_string($rawData)) {
                    return;
                }

                try {
                    $decodedData = (new JsonEncoder(
                        [JsonEncoder::DECODE_OPTIONS => \JSON_INVALID_UTF8_IGNORE | \JSON_INVALID_UTF8_SUBSTITUTE]
                    ))->decode($rawData);
                } catch (NotEncodableValueException $e) {
                    $decodedData = null;
                }

                if (!is_array($decodedData)) {
                    return;
                }

                $normalizedData = (new JsonEncoder(
                    [JsonEncoder::ENCODE_OPTIONS => \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_LINE_TERMINATORS | \JSON_INVALID_UTF8_IGNORE | \JSON_PARTIAL_OUTPUT_ON_ERROR]
                ))->encode($decodedData);

                $context->setDecodedData($normalizedData);
            },
            $processingIdentifier ?? 'normalizedJsonString-'.uniqid()
        );
    }
}
