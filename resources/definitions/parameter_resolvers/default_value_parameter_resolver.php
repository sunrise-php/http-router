<?php

declare(strict_types=1);

use Sunrise\Http\Router\ParameterResolver\DefaultValueParameterResolver;

use function DI\add;
use function DI\create;

return [
    'router.parameter_resolvers' => add([
        create(DefaultValueParameterResolver::class),
    ]),
];
