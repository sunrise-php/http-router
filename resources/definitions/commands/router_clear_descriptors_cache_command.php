<?php

declare(strict_types=1);

use Sunrise\Http\Router\Command\RouterClearDescriptorsCacheCommand;
use Sunrise\Http\Router\Loader\DescriptorLoaderInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'app.commands' => add([
        create(RouterClearDescriptorsCacheCommand::class)
            ->constructor(
                descriptorLoader: get(DescriptorLoaderInterface::class),
            ),
    ]),
];
