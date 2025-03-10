<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\Loader\DescriptorLoaderInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.descriptor_loader.resources' => [],
    'router.descriptor_loader.cache' => get(CacheInterface::class),

    'router.loaders' => add([
        get(DescriptorLoaderInterface::class),
    ]),

    DescriptorLoaderInterface::class => create(DescriptorLoader::class)
        ->constructor(
            resources: get('router.descriptor_loader.resources'),
            cache: get('router.descriptor_loader.cache'),
        ),
];
