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
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

class DataManipulatorPreset
{
    /**
     * @param array<int, mixed> $fakerArguments
     *
     * @api
     */
    public static function scalarData(string $fakerFormatter, ?string $processingIdentifier = null, ?string $scope = null, array $fakerArguments = []): DataProcessingInterface
    {
        return new DataProcessing(
            static function (DataManipulatorContext $context) use ($scope, $fakerFormatter, $fakerArguments): void {
                $scopedFaker = $context->fake(scope: $scope ?? Faker::DEFAULT_SCOPE);
                /** @var callable $callable */
                $callable = [$scopedFaker, $fakerFormatter];
                /** @var mixed $fakedData */
                $fakedData = call_user_func($callable, ...$fakerArguments);
                $context->setProcessedData($fakedData);
            },
            $processingIdentifier
        );
    }

    public static function ip(?string $processingIdentifier = null, ?string $scope = null): DataProcessingInterface
    {
        return new DataProcessing(
            static function (DataManipulatorContext $context) use ($scope): void {
                $ipData = $context->getProcessedData();

                // @codeCoverageIgnoreStart
                if (!is_string($ipData)) {
                    return;
                }
                // @codeCoverageIgnoreEnd

                if (false !== filter_var($ipData, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $context->setProcessedData(processedData: $context->fake(
                        source: $ipData,
                        scope: $scope ?? Faker::DEFAULT_SCOPE
                    )->ipv6());
                } elseif (false !== filter_var($ipData, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $context->setProcessedData(processedData: $context->fake(
                        source: $ipData,
                        scope: $scope ?? Faker::DEFAULT_SCOPE
                    )->ipv4());
                }
            },
            $processingIdentifier
        );
    }
}
