<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sunrise\Http\Router\OpenApi\Controller\SwaggerController;
use Sunrise\Http\Router\OpenApi\SwaggerConfiguration;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    SwaggerController::class => create()
        ->constructor(
            swaggerConfiguration: get(SwaggerConfiguration::class),
            responseFactory: get(ResponseFactoryInterface::class),
            streamFactory: get(StreamFactoryInterface::class),
        ),

    'router.descriptor_loader.resources' => add([
        SwaggerController::class,
    ]),
];
