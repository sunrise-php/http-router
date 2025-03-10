<?php

declare(strict_types=1);

use Sunrise\Http\Router\OpenApi\Command\RouterOpenApiBuildDocumentCommand;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;
use Sunrise\Http\Router\RouterInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'app.commands' => add([
        create(RouterOpenApiBuildDocumentCommand::class)
            ->constructor(
                router: get(RouterInterface::class),
                openApiDocumentManager: get(OpenApiDocumentManagerInterface::class),
            ),
    ]),
];
