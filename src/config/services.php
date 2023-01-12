<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(\Waldhacker\Pseudify\Core\Profile\Analyze\ProfileCollection::class)
        ->args([tagged_iterator('pseudify.analyze.profile')]);

    $services->set(\Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileCollection::class)
        ->args([tagged_iterator('pseudify.pseudonymize.profile')]);
};
