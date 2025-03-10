<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Http\Router\ResponseResolver\EncodableResponseResolver;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.encodable_response_resolver.default_media_type' => get('router.default_media_type'),
    'router.encodable_response_resolver.codec_context' => [],

    'router.response_resolvers' => add([
        create(EncodableResponseResolver::class)
            ->constructor(
                responseFactory: get(ResponseFactoryInterface::class),
                codecManager: get(CodecManagerInterface::class),
                defaultMediaType: get('router.encodable_response_resolver.default_media_type'),
                codecContext: get('router.encodable_response_resolver.codec_context'),
            ),
    ]),
];
