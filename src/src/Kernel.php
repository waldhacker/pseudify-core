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

namespace Waldhacker\Pseudify\Core;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Waldhacker\Pseudify\Core\DependencyInjection\PseudifyPass;
use Waldhacker\Pseudify\Core\Faker\FakeDataProviderInterface;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileInterface as AnalyzeProfileInterface;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface as PseudonymizeProfileInterface;

/**
 * @internal
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug, private ?string $dataDirectory = null)
    {
        if ('test' === $environment && !$this->dataDirectory) {
            $this->dataDirectory = __DIR__.'/../../build/development/userdata';
        }
        parent::__construct($environment, $debug);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.(string) $this->environment.'/*.yaml');
        $container->import('../config/{services}.yaml');
        $container->import('../config/{services}_'.(string) $this->environment.'.yaml');

        if ($this->dataDirectory && \is_dir($this->dataDirectory.'/config/')) {
            $container->import($this->dataDirectory.'/config/*.yaml');
        }

        if (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(AnalyzeProfileInterface::class)
            ->addTag('pseudify.analyze.profile');
        $container->registerForAutoconfiguration(PseudonymizeProfileInterface::class)
            ->addTag('pseudify.pseudonymize.profile');
        $container->registerForAutoconfiguration(FakeDataProviderInterface::class)
            ->addTag('pseudify.faker.provider');

        $container->addCompilerPass(new PseudifyPass());
    }

    public function getCacheDir(): string
    {
        return $this->dataDirectory ? $this->dataDirectory.'/var/cache/'.(string) $this->environment : parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        return $this->dataDirectory ? $this->dataDirectory.'/var/log' : parent::getLogDir();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
