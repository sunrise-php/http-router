<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sunrise\Http\Router\OpenApi\Controller\OpenApiController;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    OpenApiController::class => create()
        ->constructor(
            openApiConfiguration: get(OpenApiConfiguration::class),
            openApiDocumentManager: get(OpenApiDocumentManagerInterface::class),
            responseFactory: get(ResponseFactoryInterface::class),
            streamFactory: get(StreamFactoryInterface::class),
        ),

    'router.descriptor_loader.resources' => add([
        OpenApiController::class,
    ]),
];
