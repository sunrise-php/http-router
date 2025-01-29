<?php

declare(strict_types=1);

use Sunrise\Http\Router\Codec\JsonCodec;
use Sunrise\Http\Router\CodecManager;
use Sunrise\Http\Router\CodecManagerInterface;

use function DI\create;
use function DI\get;

return [
    'router.codecs' => [
        create(JsonCodec::class)
            ->constructor(
                get('router.json_codec.context'),
            ),
    ],

    'router.codecs.context' => [],
    'router.json_codec.context' => [],

    CodecManagerInterface::class => create(CodecManager::class)
        ->constructor(
            get('router.codecs'),
            get('router.codecs.context'),
        ),
];
