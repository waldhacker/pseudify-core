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
class SourceDataCollector
{
    /**
     * @param array<int, DataProcessingInterface> $processings
     *
     * @return array<array-key, int|float|bool|string>
     */
    public function process(SourceDataCollectorContext $context, ...$processings): array
    {
        foreach ($this->getValidProcessings($processings) as $processing) {
            $processor = $processing->getProcessor();
            $processor($context);
        }

        $collectedData = [];
        /** @var mixed $data */
        foreach ($context->getCollectedData() as $data) {
            if (is_array($data)) {
                $data = array_filter($data, 'is_scalar');
                $collectedData = array_merge(
                    $collectedData,
                    $data
                );
            } elseif (is_scalar($data)) {
                $collectedData = array_merge(
                    $collectedData,
                    [$data]
                );
            }
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
                throw new AmbiguousDataProcessingException(sprintf('the dataProcessing identifier "%s" must be unique.', $processing->getIdentifier()), 1619712131);
            }
            $identifiers[] = $processing->getIdentifier();
            $validProcessings[] = $processing;
        }

        return $validProcessings;
    }
}
