<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\ClassResolver;
use Sunrise\Http\Router\ClassResolverInterface;
use Sunrise\Http\Router\CodecManager;
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\MiddlewareResolver;
use Sunrise\Http\Router\MiddlewareResolverInterface;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverChainInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RequestHandlerReflector;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RequestHandlerResolver;
use Sunrise\Http\Router\RequestHandlerResolverInterface;
use Sunrise\Http\Router\ResponseResolverChain;
use Sunrise\Http\Router\ResponseResolverChainInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterInterface;

use function DI\create;
use function DI\get;

return [
    'router.loaders' => [],
    'router.middlewares' => [],
    'router.route_middlewares' => [],
    'router.parameter_resolvers' => [],
    'router.response_resolvers' => [],
    'router.event_dispatcher' => null,
    'router.codecs' => [],
    'router.codecs.context' => [],
    'router.default_media_type' => MediaType::JSON,

    ParameterResolverChainInterface::class => create(ParameterResolverChain::class)
        ->constructor(
            resolvers: get('router.parameter_resolvers'),
        ),

    ResponseResolverChainInterface::class => create(ResponseResolverChain::class)
        ->constructor(
            resolvers: get('router.response_resolvers'),
        ),

    ClassResolverInterface::class => create(ClassResolver::class)
        ->constructor(
            parameterResolverChain: get(ParameterResolverChainInterface::class),
            container: get(ContainerInterface::class),
        ),

    MiddlewareResolverInterface::class => create(MiddlewareResolver::class)
        ->constructor(
            classResolver: get(ClassResolverInterface::class),
            parameterResolverChain: get(ParameterResolverChainInterface::class),
            responseResolverChain: get(ResponseResolverChainInterface::class),
        ),

    RequestHandlerResolverInterface::class => create(RequestHandlerResolver::class)
        ->constructor(
            classResolver: get(ClassResolverInterface::class),
            parameterResolverChain: get(ParameterResolverChainInterface::class),
            responseResolverChain: get(ResponseResolverChainInterface::class),
        ),

    ReferenceResolverInterface::class => create(ReferenceResolver::class)
        ->constructor(
            middlewareResolver: get(MiddlewareResolverInterface::class),
            requestHandlerResolver: get(RequestHandlerResolverInterface::class),
        ),

    RouterInterface::class => create(Router::class)
        ->constructor(
            loaders: get('router.loaders'),
            middlewares: get('router.middlewares'),
            routeMiddlewares: get('router.route_middlewares'),
            referenceResolver: get(ReferenceResolverInterface::class),
            eventDispatcher: get('router.event_dispatcher'),
        ),

    CodecManagerInterface::class => create(CodecManager::class)
        ->constructor(
            codecs: get('router.codecs'),
            context: get('router.codecs.context'),
        ),

    RequestHandlerReflectorInterface::class => create(RequestHandlerReflector::class),
];
