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

use Waldhacker\Pseudify\Core\Processor\Processing\AmbiguousDataProcessingException;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

/**
 * @internal
 */
class DataManipulator
{
    /**
     * @param array<int, DataProcessingInterface> $processings
     */
    public function process(DataManipulatorContext $context, ...$processings): mixed
    {
        /** @var mixed $data */
        $data = $context->getDecodedData();
        $context->setProcessedData($data);

        foreach ($this->getValidProcessings($processings) as $processing) {
            $processor = $processing->getProcessor();
            $context = $context->withProcessedData($context->getProcessedData());
            $processor($context);
        }

        return $context->getProcessedData();
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
                throw new AmbiguousDataProcessingException(sprintf('the dataProcessing identifier "%s" must be unique.', $processing->getIdentifier()), 1620916028);
            }
            $identifiers[] = $processing->getIdentifier();
            $validProcessings[] = $processing;
        }

        return $validProcessings;
    }
}
