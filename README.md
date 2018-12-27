# Fast HTTP router for PHP 7.1+ based on PSR-7 and PSR-15

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://api.travis-ci.com/sunrise-php/http-router.svg?branch=master)](https://travis-ci.com/sunrise-php/http-router)
[![CodeFactor](https://www.codefactor.io/repository/github/sunrise-php/http-router/badge)](https://www.codefactor.io/repository/github/sunrise-php/http-router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

## Awards

[![SymfonyInsight](https://insight.symfony.com/projects/62934e27-3e71-439c-9569-4aa57cdb3f36/big.svg)](https://insight.symfony.com/projects/62934e27-3e71-439c-9569-4aa57cdb3f36)

## Benchmark (1k iterations with 1k routes)

> [You can see it here](https://github.com/sunrise-php/http-router-benchmark)

```
+-----------+------+--------------+-------+
| subject   | its  | mean         | diff  |
+-----------+------+--------------+-------+
| Sunrise   | 1000 | 17,856.609μs | 1.00x |
| FastRoute | 1000 | 20,920.968μs | 1.17x |
| Aura      | 1000 | 44,480.588μs | 2.49x |
| Zend      | 1000 | 96,778.725μs | 5.42x |
+-----------+------+--------------+-------+
```

## Installation (via composer)

```bash
composer require sunrise/http-router
```

## How to use?

#### QuickStart

> The example uses other sunrise packages, but you can use for example `zend/diactoros`, or any other.

```bash
composer require sunrise/http-message sunrise/http-server-request
```

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class DemoMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write(sprintf('URI: "%s"; Attributes: "%s"',
            $request->getUri(), print_r($request->getAttributes(), true)
        ));

        return $response;
    }
}

$routes = new RouteCollection();

$routes->get('home', '/')
->addMiddleware(new DemoMiddleware);

$routes->group('/api', function($routes)
{
    $routes->group('/v1', function($routes)
    {
        $routes->post('resource.create', '/resource')
        ->addMiddleware(new DemoMiddleware);

        $routes->patch('resource.update', '/resource/{id}')
        ->addPattern('id', '\d+')
        ->addMiddleware(new DemoMiddleware);

        $routes->delete('resource.delete', '/resource/{id}')
        ->addPattern('id', '\d+')
        ->addMiddleware(new DemoMiddleware);

        $routes->get('resource.read', '/resource/{id}')
        ->addPattern('id', '\d+')
        ->addMiddleware(new DemoMiddleware);

        $routes->get('resource.all', '/resource')
        ->addMiddleware(new DemoMiddleware);
    });
});

$router = new Router();
$router->addRoutes($routes);

try
{
    $response = $router->handle(ServerRequestFactory::fromGlobals());
}
catch (MethodNotAllowedException $e)
{
    $response = (new ResponseFactory)->createResponse(405)
    ->withHeader('allow', implode(',', $e->getAllowedMethods()));

    $response->getBody()->write($response->getReasonPhrase());
}
catch (RouteNotFoundException $e)
{
    $response = (new ResponseFactory)->createResponse(404);

    $response->getBody()->write($response->getReasonPhrase());
}

$headers = $response->getHeaders();

foreach ($headers as $name => $values)
{
    foreach ($values as $value)
    {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

header(sprintf('HTTP/%s %d %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
), true);

echo $response->getBody();
```

#### Adding a route to the collection

```php
// Adds a new route to the collection
$route = $routes->route('route.id', '/route/path', ['HEAD', 'GET']);

// Adds a new route to the collection that will respond to HEAD requests
$route = $routes->head('route.id', '/route/path');

// Adds a new route to the collection that will respond to GET requests
$route = $routes->get('route.id', '/route/path');

// Adds a new route to the collection that will respond to POST requests
$route = $routes->post('route.id', '/route/path');

// Adds a new route to the collection that will respond to PUT requests
$route = $routes->put('route.id', '/route/path');

// Adds a new route to the collection that will respond to PATCH requests
$route = $routes->patch('route.id', '/route/path');

// Adds a new route to the collection that will respond to DELETE requests
$route = $routes->delete('route.id', '/route/path');

// Adds a new route to the collection that will respond to PURGE requests
$route = $routes->purge('route.id', '/route/path');

// Adds a new route to the collection that will respond to safe requests
$route = $routes->safe('route.id', '/route/path');

// Adds a new route to the collection that will respond to any requests
$route = $routes->any('route.id', '/route/path');
```

#### Route grouping

```php
// Add a route to the collection with the path:
// /foo/bar/baz/qux
$routes->group('/foo', function($routes)
{
    $routes->group('/bar', function($routes)
    {
        $routes->group('/baz', function($routes)
        {
            $route = $routes->get('qux', '/qux');
        });
    });
});
```

#### Route patterns

```php
$route = $routes->any('resource.action', '/resource/{action}(/{id})')
->addPattern('action', 'create|update|delete|read|all')
->addPattern('id', '\d+');
```

#### Route middlewares

```php
$route
->addMiddleware(new FooMiddleware)
->addMiddleware(new BarMiddleware)
->addMiddleware(new BazMiddleware);
```

#### Router middlewares

```php
$router
->addMiddleware(new FooMiddleware)
->addMiddleware(new BarMiddleware)
->addMiddleware(new BazMiddleware);
```

#### Router matching

```php
$route = $router->match($request);
```

## Useful Middlewares

#### Error handling ([whoops](https://github.com/filp/whoops))

```bash
composer require middlewares/whoops
```

```php
$router->addMiddleware(new \Middlewares\Whoops());
```

#### Payload ([payload](https://github.com/middlewares/payload))

```bash
composer require middlewares/payload
```

```php
$router->addMiddleware(new \Middlewares\JsonPayload());
$router->addMiddleware(new \Middlewares\UrlEncodePayload());
```

#### Encoding ([encoder](https://github.com/middlewares/encoder))

```bash
composer require middlewares/encoder
```

```php
$router->addMiddleware(new \Middlewares\GzipEncoder());
```

## Awesome PSR-15 Middlewares

https://github.com/middlewares

## Test run

```bash
php vendor/bin/phpunit
```

## Benchmark run

https://github.com/sunrise-php/http-router-benchmark

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-15/
* https://github.com/middlewares
