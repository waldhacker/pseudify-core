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

namespace Waldhacker\Pseudify\Core\DependencyInjection;

use Faker\Generator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
class PseudifyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $fakerGeneratorDefinition = $container->findDefinition(Generator::class);

        foreach (array_keys($container->findTaggedServiceIds('pseudify.faker.provider')) as $serviceName) {
            $definition = $container->findDefinition($serviceName);
            $definition->setArgument('$generator', new Reference(Generator::class));
            $fakerGeneratorDefinition->addMethodCall('addProvider', [new Reference($serviceName)]);
        }
    }
}
