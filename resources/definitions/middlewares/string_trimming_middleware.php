<?php

declare(strict_types=1);

use Sunrise\Http\Router\Middleware\StringTrimmingMiddleware;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.string_trimming_middleware.trimmer' => null,

    'router.route_middlewares' => add([
        create(StringTrimmingMiddleware::class)
            ->constructor(
                trimmer: get('router.string_trimming_middleware.trimmer'),
            ),
    ]),
];
