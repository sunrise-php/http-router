<?php

declare(strict_types=1);

use Sunrise\Http\Router\Codec\JsonCodec;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.json_codec.context' => [],

    'router.codecs' => add([
        create(JsonCodec::class)
            ->constructor(
                context: get('router.json_codec.context'),
            ),
    ]),
];
