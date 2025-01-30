<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Sunrise\Http\Router\ResponseResolver\EmptyResponseResolver;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.response_resolvers' => add([
        create(EmptyResponseResolver::class)
            ->constructor(
                responseFactory: get(ResponseFactoryInterface::class),
            ),
    ]),
];
