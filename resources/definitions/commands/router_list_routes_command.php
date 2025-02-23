<?php

declare(strict_types=1);

use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouterInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'app.commands' => add([
        create(RouterListRoutesCommand::class)
            ->constructor(
                router: get(RouterInterface::class),
            ),
    ]),
];
