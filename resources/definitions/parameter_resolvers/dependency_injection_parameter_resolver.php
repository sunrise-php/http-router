<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\ParameterResolver\DependencyInjectionParameterResolver;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.parameter_resolvers' => add([
        create(DependencyInjectionParameterResolver::class)
            ->constructor(
                container: get(ContainerInterface::class),
            ),
    ]),
];
