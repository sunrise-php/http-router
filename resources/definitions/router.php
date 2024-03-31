<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\ClassResolver;
use Sunrise\Http\Router\ClassResolverInterface;
use Sunrise\Http\Router\Entity\Language\LanguageComparator;
use Sunrise\Http\Router\Entity\Language\LanguageComparatorInterface;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparatorInterface;
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
    'router.route_middlewares' => [],
    'router.parameter_resolvers' => [],
    'router.response_resolvers' => [],
    'router.event_dispatcher' => null,

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
            loaders: get('router.loaders'),
            middlewares: get('router.middlewares'),
            routeMiddlewares: get('router.route_middlewares'),
            referenceResolver: get(ReferenceResolverInterface::class),
            eventDispatcher: get('router.event_dispatcher'),
        ),

    MediaTypeComparatorInterface::class => create(MediaTypeComparator::class),
    LanguageComparatorInterface::class => create(LanguageComparator::class),
];
