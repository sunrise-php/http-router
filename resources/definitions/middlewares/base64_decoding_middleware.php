<?php

declare(strict_types=1);

use Psr\Http\Message\StreamFactoryInterface;
use Sunrise\Http\Router\Middleware\Base64DecodingMiddleware;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.route_middlewares' => add([
        create(Base64DecodingMiddleware::class)
            ->constructor(
                streamFactory: get(StreamFactoryInterface::class),
            )
    ]),
];
