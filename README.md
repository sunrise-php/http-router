# HTTP router for PHP 7.1+ based on PSR-7 and PSR-15 with support for annotations/attributes and OpenAPI (Swagger) Specification

**psr router**, **router with annotations**, **router with attributes**, **php router**.

[![Build Status](https://circleci.com/gh/sunrise-php/http-router.svg?style=shield)](https://circleci.com/gh/sunrise-php/http-router)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

---

## Installation

```bash
composer require 'sunrise/http-router:^2.15'
```

## Support for OpenAPI (Swagger) Specification (optional)

```bash
composer require 'sunrise/http-router-openapi:^2.0'
```

More details can be found here: [sunrise/http-router-openapi](https://github.com/sunrise-php/http-router-openapi).

## QuickStart

This example uses other sunrise packages, but you can use e.g. `zend/diactoros` or any other.

```bash
composer require sunrise/http-message sunrise/http-server-request
```

```php
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

use function Sunrise\Http\Router\emit;

$collector = new RouteCollector();

// PSR-15 request handler (optimal performance):
$collector->get('home', '/', new HomeRequestHandler());

// or you can use an anonymous function as your request handler:
$collector->get('home', '/', function ($request) {
    return (new ResponseFactory)->createResponse(200);
});

// or you can use the name of a class that implements PSR-15:
$collector->get('home', '/', HomeRequestHandler::class);

// or you can use a class method name as your request handler:
// (note that such a class mayn't implement PSR-15)
$collector->get('home', '/', [HomeRequestHandler::class, 'index']);

// most likely you will need to use PSR-11 container:
// (note that only named classes will be pulled from such a container)
$collector->setContainer($container);

$router = new Router();
$router->addRoute(...$collector->getCollection()->all());

$request = ServerRequestFactory::fromGlobals();
$response = $router->handle($request);

emit($response);
```

---

## Examples of using

Study [sunrise/awesome-skeleton](https://github.com/sunrise-php/awesome-skeleton) to understand how this can be used.

#### Strategy for loading routes from configs

> Please note that since version 2.10.0 class `ConfigLoader` must be used.

```php
use Sunrise\Http\Router\Loader\ConfigLoader;
use Sunrise\Http\Router\Router;

$loader = new ConfigLoader();

// set container if necessary...
$loader->setContainer($container);

// attach configs...
$loader->attach('routes/api.php');
$loader->attach('routes/admin.php');
$loader->attach('routes/public.php');

// or attach a directory...
// [!] available from version 2.2
$loader->attach('routes');

// or attach an array...
// [!] available from version 2.4
$loader->attachArray([
    'routes/api.php',
    'routes/admin.php',
    'routes/public.php',
]);

// install container if necessary...
$loader->setContainer($container);

$router = new Router();
$router->load($loader);

// if the router matching should be isolated for top middlewares...
// for example for error handling...
// [!] available from version 2.8
$response = $router->run($request);

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

```php
/** @var Sunrise\Http\Router\RouteCollector $this */

$this->get('home', '/', new CallableRequestHandler(function ($request) {
    return (new ResponseFactory)->createJsonResponse(200);
}));

// or using a direct reference to a request handler...
$this->get('home', '/', new App\Http\Controller\HomeController());
```

> Please note that since version 2.10.0 you can refer to the request handler in different ways.

```php
/** @var Sunrise\Http\Router\RouteCollector $this */

$this->get('home', '/', function ($request) {
    return (new ResponseFactory)->createJsonResponse(200);
});

$this->get('home', '/', App\Http\Controller\HomeController::class, [
    App\Http\Middleware\FooMiddleware::class,
    App\Http\Middleware\BarMiddleware::class,
]);

$this->get('home', '/', [App\Http\Controller\HomeController::class, 'index'], [
    App\Http\Middleware\FooMiddleware::class,
    App\Http\Middleware\BarMiddleware::class,
]);
```

#### Strategy for loading routes from descriptors (annotations or attributes)

Install the [doctrine/annotations](https://github.com/doctrine/annotations) package if you will be use annotations:

```bash
composer require doctrine/annotations
```

> Please note that since version 2.10.0 class `DescriptorLoader` must be used.

> Please note that since version 2.10.0 you can bind the @Rote() annotation to a class methods.

```php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\Router;

// necessary if you will use annotations (annotations isn't attributes)...
AnnotationRegistry::registerLoader('class_exists');

$loader = new DescriptorLoader();

// set container if necessary...
$loader->setContainer($container);

// attach a directory with controllers...
$loader->attach('src/Controller');

// or attach an array
// [!] available from version 2.4
$loader->attachArray([
    'src/Controller',
    'src/Bundle/BundleName/Controller',
]);

// or attach a class only
// [!] available from 2.10 version.
$loader->attach(App\Http\Controller\FooController::class);

$router = new Router();
$router->load($loader);

// if the router matching should be isolated for top middlewares...
// for example for error handling...
// [!] available from version 2.8
$response = $router->run($request);

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

```php
use Sunrise\Http\Router\Annotation as Mapping;

#[Mapping\Prefix('/api/v1')]
#[Mapping\Middleware(SomeMiddleware::class)]
class SomeController {

    #[Mapping\Route('foo', path: '/foo')]
    public function foo() {
        // will be available at: /api/v1/foo
    }

    #[Mapping\Route('bar', path: '/bar')]
    public function bar() {
        // will be available at: /api/v1/bar
    }
}
```

#### Without loading strategy

```php
use App\Controller\HomeController;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;

$collector = new RouteCollector();

// set container if necessary...
$collector->setContainer($container);

$collector->get('home', '/', new HomeController());

$router = new Router();
$router->addRoute(...$collector->getCollection()->all());

// if the router matching should be isolated for top middlewares...
// for example for error handling...
// [!] available from version 2.8
$response = $router->run($request);

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

#### Error handling example

```php
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

use function Sunrise\Http\Router\emit;

$collector = new RouteCollector();

$collector->get('home', '/', new CallableRequestHandler(function ($request) {
    return (new ResponseFactory)->createJsonResponse(200);
}));

$router = new Router();
$router->addRoute(...$collector->getCollection()->all());

$router->addMiddleware(new CallableMiddleware(function ($request, $handler) {
    try {
        return $handler->handle($request);
    } catch (MethodNotAllowedException $e) {
        return (new ResponseFactory)->createResponse(405);
    } catch (RouteNotFoundException $e) {
        return (new ResponseFactory)->createResponse(404);
    } catch (Throwable $e) {
        return (new ResponseFactory)->createResponse(500);
    }
}));

emit($router->run(ServerRequestFactory::fromGlobals()));
```

#### Work with PSR-11 container

##### Collector

```php
$collector = new RouteCollector();

/** @var \Psr\Container\ContainerInterface $container */

// Pass DI container to the collector...
$collector->setContainer($container);

// Objects passed as strings will be initialized through the DI container...
$route = $collector->get('home', '/', HomeController::class, [
    FooMiddleware::class,
    BarMiddleware::class,
]);
```

##### Config loader

```php
$loader = new ConfigLoader();

/** @var \Psr\Container\ContainerInterface $container */

// Pass DI container to the loader...
$loader->setContainer($container);

// All found objects which has been passed as strings will be initialized through the DI container...
$routes = $loader->load();
```

##### Descriptor loader

```php
$loader = new DescriptorLoader();

/** @var \Psr\Container\ContainerInterface $container */

// Pass DI container to the loader...
$loader->setContainer($container);

// All found objects will be initialized through the DI container...
$routes = $loader->load();
```

#### Descriptors cache (PSR-16)

```php
$loader = new DescriptorLoader();

/** @var \Psr\SimpleCache\CacheInterface $cache */

// Pass a cache to the loader...
$loader->setCache($cache);
```

#### Route Annotation Example

##### Minimal annotation view

```php
/**
 * @Route(
 *   name="api_v1_entry_update",
 *   path="/api/v1/entry/{id<@uuid>}(/{optionalAttribute})",
 *   methods={"PATCH"},
 * )
 */
final class EntryUpdateRequestHandler implements RequestHandlerInterface
```

##### Full annotation

```php
/**
 * @Route(
 *   name="api_v1_entry_update",
 *   host="api.host",
 *   path="/api/v1/entry/{id<@uuid>}(/{optionalAttribute})",
 *   methods={"PATCH"},
 *   middlewares={
 *     "App\Middleware\CorsMiddleware",
 *     "App\Middleware\ApiAuthMiddleware",
 *   },
 *   attributes={
 *     "optionalAttribute": "defaultValue",
 *   },
 *   summary="Updates an entry by UUID",
 *   description="Here you can describe the method in more detail...",
 *   tags={"api", "entry"},
 *   priority=0,
 * )
 */
final class EntryUpdateRequestHandler implements RequestHandlerInterface
```

##### One method only

```php
/**
 * @Route(
 *   name="home",
 *   path="/",
 *   method="GET",
 * )
 */
```

#### Route Attribute Example

##### Minimal attribute view

```php
use Sunrise\Http\Router\Annotation\Route;

#[Route(
    name: 'api_v1_entry_update',
    path: '/api/v1/entry/{id<@uuid>}(/{optionalAttribute})',
    methods: ['PATCH'],
)]
final class EntryUpdateRequestHandler implements RequestHandlerInterface
```

##### Full attribute

```php
use Sunrise\Http\Router\Annotation\Route;

#[Route(
    name: 'api_v1_entry_update',
    host: 'api.host',
    path: '/api/v1/entry/{id<@uuid>}(/{optionalAttribute})',
    methods: ['PATCH'],
    middlewares: [
        \App\Middleware\CorsMiddleware::class,
        \App\Middleware\ApiAuthMiddleware::class,
    ],
    attributes: [
        'optionalAttribute' => 'defaultValue',
    ],
    summary: 'Updates an entry by UUID',
    description: 'Here you can describe the method in more detail...',
    tags: ['api', 'entry'],
    priority: 0,
)]
final class EntryUpdateRequestHandler implements RequestHandlerInterface
```

##### Additional annotations

```php
use Sunrise\Http\Router\Annotation\Host;

#[Host('admin')]
#[Prefix('/api/v1')]
#[Postfix('.json')]
#[Middleware(SomeMiddleware::class)]
final class SomeController
{
    #[Route('foo', '/foo')]
    public function foo(ServerRequestInterface $request) : ResponseInterface
    {
        // this action will be available at:
        // http://admin.host/api/v1/foo.json
        //
        // this can be handy to reduce code duplication...
    }
}
```

---

## Useful to know

### JSON-payload decoding

```php
use Sunrise\Http\Router\Middleware\JsonPayloadDecodingMiddleware;

$router->addMiddleware(new JsonPayloadDecodingMiddleware());
```

### Get a route by name

```php
// checks if a route is exists
$router->hasRoute('foo');

// gets a route by name
$router->getRoute('foo');
```

### Get a current route

#### Through Router

> Available from version 2.12.

```php
$router->getMatchedRoute();
```

#### Through Request

> Available from version 1.x, but wasn't documented before...

```php
$request->getAttribute('@route');

// or
$request->getAttribute(\Sunrise\Http\Router\RouteInterface::ATTR_ROUTE);
```

#### Through Event

> Available from version 2.13.

```php
$eventDispatcher->addListener(RouteEvent::NAME, function (RouteEvent $event) {
    $event->getRoute();
});
```

### Generation a route URI

```php
$uri = $router->generateUri('route.name', [
    'attribute' => 'value',
], true);
```

### Run a route

```php
$response = $router->getRoute('route.name')->handle($request);
```

### Route grouping

Example for annotations [here](#additional-annotations).

```php
$collector->group(function ($collector) {
    $collector->group(function ($collector) {
        $collector->group(function ($collector) {
            $collector->get('api.entry.read', '/{id<\d+>}', ...)
                ->addMiddleware(...); // add the middleware(s) to the route...
        })
        ->addPrefix('/entry') // add the prefix to the group...
        ->prependMiddleware(...); // add the middleware(s) to the group...
    }, [
        App\Http\Middleware\Bar::class, // resolvable middlewares...
    ])
    ->addPrefix('/v1') // add the prefix to the group...
    ->prependMiddleware(...); // add the middleware(s) to the group...
}, [
    App\Http\Middleware\Foo::class, // resolvable middlewares...
])
->addPrefix('/api') // add the prefix to the group...
->prependMiddleware(...); // add the middleware(s) to the group...
```

### Route patterns

```php
$collector->get('api.entry.read', '/api/v1/entry/{id<\d+>}(/{optional<\w+>})');
```

##### Global route patterns

```php
// @uuid pattern
$collector->get('api.entry.read', '/api/v1/entry/{id<@uuid>}');

// @slug pattern
$collector->get('api.entry.read', '/api/v1/entry/{slug<@slug>}');

// Custom patterns (available from version 2.9.0):
\Sunrise\Http\Router\Router::$patterns['@id'] = '[1-9][0-9]*';

// Just use the custom pattern...
$collector->get('api.entry.read', '/api/v1/entry/{id<@id>}');
```

It is better to set patterns through the router:

```php
// available since version 2.11.0
$router->addPatterns([
    '@id' => '[1-9][0-9]*',
]);
```

...or through the router's builder:

```php
// available since version 2.11.0
$builder->setPatterns([
    '@id' => '[1-9][0-9]*',
]);
```

### Hosts (available from version 2.6.0)

> Note: if you don't assign a host for a route, it will be available on any hosts!

```php
// move the hosts table into the settings...
$router->addHost('public.host', 'www.example.com', ...);
$router->addHost('admin.host', 'secret.example.com', ...);
$router->addHost('api.host', 'api.example.com', ...);

// ...or:
$router->addHosts([
    'public.host' => ['www.example.com', ...],
    ...
]);

// the route will available only on the `secret.example.com` host...
$route->setHost('admin.host');

// routes in the group will available on the `secret.example.com` host...
$collector->group(function ($collector) {
    // some code...
})
->setHost('admin.host');
```

You can resolve the hostname since version 2.14.0 as follows:

```php
$router->addHost('admin', 'www1.admin.example.com', 'www2.admin.example.com');

$router->resolveHostname('www1.admin.example.com'); // return "admin"
$router->resolveHostname('www2.admin.example.com'); // return "admin"
$router->resolveHostname('unknown'); // return null
```

Also you can get all routes by hostname:

```php
$router->getRoutesByHostname('www1.admin.example.com');
```

### Route Holder

```php
$route->getHolder(); // return Reflector (class, method or function)
```

### The router builder

```php
$router = (new RouterBuilder)
    ->setEventDispatcher(...) // null or use to symfony/event-dispatcher...
    ->setContainer(...) // null or PSR-11 container instance...
    ->setCache(...) // null or PSR-16 cache instance... (only for descriptor loader)
    ->setCacheKey(...) // null or string... (only for descriptor loader)
    ->useConfigLoader([]) // array with files or directory with files...
    ->useDescriptorLoader([]) // array with classes or directory with classes...
    ->setHosts([]) //
    ->setMiddlewares([]) // array with middlewares...
    ->setPatterns([]) // available since version 2.11.0
    ->build();
```

### CLI commands

```php
use Sunrise\Http\Router\Command\RouteListCommand;

new RouteListCommand($router);
```

### Events

> Available from version 2.13

```bash
composer require symfony/event-dispatcher
```

```php
use Sunrise\Http\Router\Event\RouteEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

$eventDispatcher = new EventDispatcher();

$eventDispatcher->addListener(RouteEvent::NAME, function (RouteEvent $event) {
    // gets the matched route:
    $event->getRoute();
    // gets the current request:
    $event->getRequest();
    // overrides the current request:
    $event->setRequest(ServerRequestInterface $request);
});

$router->setEventDispatcher($eventDispatcher);
```

---

## Test run

```bash
composer test
```

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-15/
* https://github.com/sunrise-php/awesome-skeleton
* https://github.com/middlewares
