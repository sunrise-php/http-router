# HTTP router for PHP 7.1+ (incl. PHP 8 with attributes) based on PSR-7 and PSR-15 with support for annotations and OpenApi (Swagger)

[![Build Status](https://circleci.com/gh/sunrise-php/http-router.svg?style=shield)](https://circleci.com/gh/sunrise-php/http-router)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

---

## Installation

```bash
composer require 'sunrise/http-router:^2.9'
```

## QuickStart

The example uses other sunrise packages, but you can use, for example, `zend/diactoros` or any other.

```bash
composer require sunrise/http-message sunrise/http-server-request
```

```php
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

use function Sunrise\Http\Router\emit;

$collector = new RouteCollector();

$collector->get('home', '/', new CallableRequestHandler(function ($request) {
    return (new ResponseFactory)->createJsonResponse(200, [
        'status' => 'ok',
    ]);
}));

$router = new Router();
$router->addRoute(...$collector->getCollection()->all());

$request = ServerRequestFactory::fromGlobals();
$response = $router->handle($request);

emit($response);
```

---

## Examples of using

Study [sunrise/awesome-skeleton](https://github.com/sunrise-php/awesome-skeleton) to understand how this can be used.

#### Strategy loading routes from configs

```php
use Sunrise\Http\Router\Loader\CollectableFileLoader;
use Sunrise\Http\Router\Router;

$loader = new CollectableFileLoader();
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
```

#### Strategy loading routes from descriptors (annotations or attributes)

Install the [doctrine/annotations](https://github.com/doctrine/annotations) package if you will be use annotations:

```bash
composer require doctrine/annotations
```

```php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sunrise\Http\Router\Loader\DescriptorDirectoryLoader;
use Sunrise\Http\Router\Router;

// necessary if you will use annotations (annotations isn't attributes)...
AnnotationRegistry::registerLoader('class_exists');

$loader = new DescriptorDirectoryLoader();
$loader->attach('src/Controller');

// or attach an array
// [!] available from version 2.4
$loader->attachArray([
    'src/Controller',
    'src/Bundle/BundleName/Controller',
]);

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

#### Without loading strategy

```php
use App\Controller\HomeController;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;

$collector = new RouteCollector();
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
$loader = new CollectableFileLoader();

/** @var \Psr\Container\ContainerInterface $container */

// Pass DI container to the loader...
$loader->setContainer($container);

// All found objects which has been passed as strings will be initialized through the DI container...
$routes = $loader->load();
```

##### Descriptor loader

```php
$loader = new DescriptorDirectoryLoader();

/** @var \Psr\Container\ContainerInterface $container */

// Pass DI container to the loader...
$loader->setContainer($container);

// All found objects will be initialized through the DI container...
$routes = $loader->load();
```

#### Descriptors cache (PSR-16)

```php
$loader = new DescriptorDirectoryLoader();

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
use Sunrise\Http\Router\Attribute\Route;

#[Route(
    name: 'api_v1_entry_update',
    path: '/api/v1/entry/{id<@uuid>}(/{optionalAttribute})',
    methods: ['PATCH'],
)]
final class EntryUpdateRequestHandler implements RequestHandlerInterface
```

##### Full attribute

```php
use Sunrise\Http\Router\Attribute\Route;

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

---

## Useful to know

### OpenApi (Swagger)

```bash
composer require 'sunrise/http-router-openapi:^1.1'
```

#### Generation documentation for Swagger (OAS)

```php
use Sunrise\Http\Router\OpenApi\Object\Info;
use Sunrise\Http\Router\OpenApi\OpenApi;

$openApi = new OpenApi(new Info('0.0.1', 'API'));

$openApi->addRoute(...$router->getRoutes());

$openApi->toArray();
```

#### Validation a request body via Swagger documentation

```php
use Sunrise\Http\Router\OpenApi\Middleware\RequestBodyValidationMiddleware;

$route->addMiddleware(new RequestBodyValidationMiddleware());
```

or using annotations:

```php
/**
 * @Route(
 *   name="foo",
 *   path="/foo",
 *   methods={"post"},
 *   middlewares={
 *     "Sunrise\Http\Router\OpenApi\Middleware\RequestBodyValidationMiddleware",
 *   },
 * )
 */
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

```php
$collector->group(function ($collector) {
    $collector->group(function ($collector) {
        $collector->group(function ($collector) {
            $collector->get('api.entry.read', '/{id<\d+>}', ...)
                ->addMiddleware(...); // add the middleware(s) to the route...
        })
        ->addPrefix('/entry') // add the prefix to the group...
        ->prependMiddleware(...); // add the middleware(s) to the group...
    })
    ->addPrefix('/v1') // add the prefix to the group...
    ->prependMiddleware(...); // add the middleware(s) to the group...
})
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
\Sunrise\Http\Router\Route::$patterns['@id'] = '[1-9][0-9]*';

// Just use the custom pattern...
$collector->get('api.entry.read', '/api/v1/entry/{id<@id>}');
```

### Hosts (available from version 2.6.0)

> Note: if you don't assign a host for a route, it will be available on any hosts!

```php
// move the hosts table into the settings...
$router->addHost('public.host', 'www.example.com');
$router->addHost('admin.host', 'secret.example.com');
$router->addHost('api.host', 'api.example.com');

// the route will available only on the `secret.example.com` host...
$route->setHost('admin.host');

// routes in the group will available on the `secret.example.com` host...
$collector->group(function ($collector) {
    // some code...
})
->setHost('admin.host');
```

### CLI commands

```php
use Sunrise\Http\Router\Command\RouteListCommand;

new RouteListCommand($router);
```

---

## Test run

```bash
composer test
```

---

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-15/
* https://github.com/sunrise-php/awesome-skeleton
* https://github.com/middlewares
