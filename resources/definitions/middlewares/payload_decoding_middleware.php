<?php

declare(strict_types=1);

use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Http\Router\Middleware\PayloadDecodingMiddleware;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.payload_decoding_middleware.codec_context' => [],

    'router.route_middlewares' => add([
        create(PayloadDecodingMiddleware::class)
            ->constructor(
                codecManager: get(CodecManagerInterface::class),
                codecContext: get('router.payload_decoding_middleware.codec_context'),
            )
    ]),
];
