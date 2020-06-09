# HTTP router for PHP 7.1+ based on PSR-7 and PSR-15 with support for annotations and OpenApi (Swagger)

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fsunrise-php%2Fhttp-router.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fsunrise-php%2Fhttp-router?ref=badge_shield)

---

## Installation

```bash
composer require 'sunrise/http-router:^2.4'
```

## QuickStart

The example uses other sunrise packages, but you can use for example `zend/diactoros`, or any other.

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

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

#### Strategy loading routes from annotations

```php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sunrise\Http\Router\Loader\AnnotationDirectoryLoader;
use Sunrise\Http\Router\Router;

AnnotationRegistry::registerLoader('class_exists');

$loader = new AnnotationDirectoryLoader();
$loader->attach('src/Controller');

// or attach an array
// [!] available from version 2.4
$loader->attachArray([
    'src/Controller',
    'src/Bundle/BundleName/Controller',
]);

$router = new Router();
$router->load($loader);

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

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

#### Route Annotation Example

```php
/**
 * @Route(
 *   name="apiEntryUpdate",
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
        ->unshiftMiddleware(...); // add the middleware(s) to the group...
    })
    ->addPrefix('/v1') // add the prefix to the group...
    ->unshiftMiddleware(...); // add the middleware(s) to the group...
})
->addPrefix('/api') // add the prefix to the group...
->unshiftMiddleware(...); // add the middleware(s) to the group...
```

### Route patterns

```php
$collector->get('api.entry.read', '/api/v1/entry/{id<\d+>}(/{optional<\w+>})');
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


## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fsunrise-php%2Fhttp-router.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fsunrise-php%2Fhttp-router?ref=badge_large)