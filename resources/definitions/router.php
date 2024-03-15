<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\ClassResolver;
use Sunrise\Http\Router\ClassResolverInterface;
use Sunrise\Http\Router\MiddlewareResolver;
use Sunrise\Http\Router\MiddlewareResolverInterface;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverChainInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RequestHandlerResolver;
use Sunrise\Http\Router\RequestHandlerResolverInterface;
use Sunrise\Http\Router\ResponseResolverChain;
use Sunrise\Http\Router\ResponseResolverChainInterface;
use Sunrise\Http\Router\Router;

use function DI\create;
use function DI\get;

return [
    'router.loaders' => [],

    'router.middlewares' => [],

    ParameterResolverChainInterface::class => create(ParameterResolverChain::class)
        ->constructor(
            get('router.parameter_resolvers'),
        ),

    ResponseResolverChainInterface::class => create(ResponseResolverChain::class)
        ->constructor(
            get('router.response_resolvers'),
        ),

    ClassResolverInterface::class => create(ClassResolver::class)
        ->constructor(
            get(ParameterResolverChainInterface::class),
            get(ContainerInterface::class),
        ),

    MiddlewareResolverInterface::class => create(MiddlewareResolver::class)
        ->constructor(
            get(ClassResolverInterface::class),
            get(ParameterResolverChainInterface::class),
            get(ResponseResolverChainInterface::class),
        ),

    RequestHandlerResolverInterface::class => create(RequestHandlerResolver::class)
        ->constructor(
            get(ClassResolverInterface::class),
            get(ParameterResolverChainInterface::class),
            get(ResponseResolverChainInterface::class),
        ),

    ReferenceResolverInterface::class => create(ReferenceResolver::class)
        ->constructor(
            get(MiddlewareResolverInterface::class),
            get(RequestHandlerResolverInterface::class),
        ),

    Router::class => create()
        ->constructor(
            get('router.loaders'),
            get('router.middlewares'),
            get(ReferenceResolverInterface::class),
        ),
];
