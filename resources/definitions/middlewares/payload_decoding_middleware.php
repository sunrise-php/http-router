<?php

declare(strict_types=1);

use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Middleware\PayloadDecodingMiddleware;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.payload_decoding_middleware.codec_context' => [],
    'router.payload_decoding_middleware.error_status_code' => null,
    'router.payload_decoding_middleware.error_message' => null,

    'router.route_middlewares' => add([
        create(PayloadDecodingMiddleware::class)
            ->constructor(
                codecManager: get(CodecManagerInterface::class),
                codecContext: get('router.payload_decoding_middleware.codec_context'),
                errorStatusCode: get('router.payload_decoding_middleware.error_status_code'),
                errorMessage: get('router.payload_decoding_middleware.error_message'),
            )
    ]),
];
