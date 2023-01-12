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

use Waldhacker\Pseudify\Core\Processor\Processing\AmbiguousDataProcessingException;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

/**
 * @internal
 */
class TargetDataDecoder
{
    /**
     * @param array<int, DataProcessingInterface> $processings
     *
     * @return array<array-key, int|float|bool|string>
     */
    public function process(TargetDataDecoderContext $context, ...$processings): array
    {
        foreach ($this->getValidProcessings($processings) as $processing) {
            $processor = $processing->getProcessor();
            $context = $context->withDecodedData($context->getDecodedData());
            $processor($context);
        }

        /** @var mixed $decodedData */
        $decodedData = $context->getDecodedData();

        $collectedData = [];
        if (is_array($decodedData)) {
            $collectedData = array_filter($decodedData, 'is_scalar');
        } elseif (is_scalar($decodedData)) {
            $collectedData = [$decodedData];
        }

        return $collectedData;
    }

    /**
     * @return array<int, DataProcessingInterface>
     */
    private function getValidProcessings(array $allProcessings): array
    {
        $validProcessings = [];
        $identifiers = [];
        foreach ($allProcessings as $processing) {
            if (!$processing instanceof DataProcessingInterface) {
                continue;
            }

            if (in_array($processing->getIdentifier(), $identifiers, true)) {
                throw new AmbiguousDataProcessingException(sprintf('the dataProcessing identifier "%s" must be unique.', $processing->getIdentifier()), 1621683731);
            }
            $identifiers[] = $processing->getIdentifier();
            $validProcessings[] = $processing;
        }

        return $validProcessings;
    }
}
