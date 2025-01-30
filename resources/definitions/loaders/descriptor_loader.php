<?php

declare(strict_types=1);

use Sunrise\Http\Router\Loader\DescriptorLoader;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.descriptor_loader.resources' => [],
    'router.descriptor_loader.cache' => null,

    'router.loaders' => add([
        create(DescriptorLoader::class)
            ->constructor(
                resources: get('router.descriptor_loader.resources'),
                cache: get('router.descriptor_loader.cache'),
            ),
    ]),
];
