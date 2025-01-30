<?php

declare(strict_types=1);

use Sunrise\Http\Router\Middleware\PayloadNegotiationMiddleware;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.payload_negotiation_middleware.error_status_code' => null,
    'router.payload_negotiation_middleware.error_message' => null,

    'router.route_middlewares' => add([
        create(PayloadNegotiationMiddleware::class)
            ->constructor(
                errorStatusCode: get('router.payload_negotiation_middleware.error_status_code'),
                errorMessage: get('router.payload_negotiation_middleware.error_message'),
            )
    ]),
];
