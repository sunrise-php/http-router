# HTTP Router for PHP 7.2+ based on PSR-7 and PSR-15

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://api.travis-ci.com/sunrise-php/http-router.svg?branch=master)](https://travis-ci.com/sunrise-php/http-router)
[![CodeFactor](https://www.codefactor.io/repository/github/sunrise-php/http-router/badge)](https://www.codefactor.io/repository/github/sunrise-php/http-router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

## Installation

```
composer require sunrise/http-router
```

## How to use?

#### QuickStart

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

$router = new Router();

$router->get('home', '/', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
{
    $response->getBody()->write(
    	$request->getAttribute('@route')
    );

    return $response;
});

$request = ServerRequestFactory::fromGlobals();

$response = $router->handle($request);
```

#### Grouping

```php
use Sunrise\Http\Router\RouteCollectionInterface;

$router->group('/api', function(RouteCollectionInterface $collection)
{
    $collection->middleware(...);

    $collection->group('/v1', function(RouteCollectionInterface $collection)
    {
        $collection->middleware(...);

        $collection->group('/post', function(RouteCollectionInterface $collection)
        {
            $collection->middleware(...);

            $collection->get(...);
        });

        $collection->group('/user', function(RouteCollectionInterface $collection)
        {
            $collection->middleware(...);

            $collection->get(...);
        });
    });
});
```

#### Routes

```php
// Register a new route
$router->add($id, $path, $action);
// Register a new route that will respond to HEAD requests
$router->head($id, $path, $action);
// Register a new route that will respond to GET requests
$router->get($id, $path, $action);
// Register a new route that will respond to POST requests
$router->post($id, $path, $action);
// Register a new route that will respond to PUT requests
$router->put($id, $path, $action);
// Register a new route that will respond to PATCH requests
$router->patch($id, $path, $action);
// Register a new route that will respond to DELETE requests
$router->delete($id, $path, $action);
// Register a new route that will respond to PURGE requests
$router->purge($id, $path, $action);
// Register a new route that will respond to OPTIONS requests
$router->options($id, $path, $action);
// Register a new route that will respond to TRACE requests
$router->trace($id, $path, $action);
// Register a new route that will respond to CONNECT requests
$router->connect($id, $path, $action);
// Register a new route that will respond to safe requests
$router->safe($id, $path, $action);
// Register a new route that will respond to any requests
$router->any($id, $path, $action);
```

#### Methods ([http-message-util](https://github.com/php-fig/http-message-util))

```php
$router->add($id, $path, $action)
->method('HEAD')
->method('GET')
->method('POST')
->method('PUT')
->method('PATCH')
->method('DELETE')
->method('PURGE')
->method('OPTIONS')
->method('TRACE')
->method('CONNECT');
```

#### Patterns

```php
$router->any($id, '/post/{id}/{action}', $action)
->pattern('id', '\d+')
->pattern('action', 'read|update|delete');
```

#### Middlewares ([awesome-psr15-middlewares](https://github.com/middlewares/awesome-psr15-middlewares))

```php
// Adds a new middleware to the router
$router->middleware(new MiddlewareFoo())
->middleware(new MiddlewareBar());

// Adds a new middleware to the route
$router->add($id, $path, $action)
->middleware(new MiddlewareBaz())
->middleware(new MiddlewareQux());
```

#### Attributes

```php
$router->get('post.read', '/post/{id}', $action);

// Gets the route ID
$request->getAttribute('@route');

// Gets the route attribute "id"
$request->getAttribute('id');
```

#### Handling 404 (Page Not Found)

```php
use Sunrise\Http\Router\Exception\PageNotFoundException;

try
{
    $response = $router->handle(...);
}
catch (PageNotFoundException $e)
{
    $response = $e->getResponse();
}
```

#### Handling 405 (Method Not Allowed)

```php
use Sunrise\Http\Router\Exception\MethodNotAllowedException;

try
{
    $response = $router->handle(...);
}
catch (MethodNotAllowedException $e)
{
    $response = $e->getResponse();

    // Receipt the allowed methods...
    $allowedMethods = $e->getAllowedMethods();
}
```

#### Handling other HTTP errors

```php
use Sunrise\Http\Router\Exception\HttpException;

try
{
    $response = $router->handle(...);
}
catch (HttpException $e)
{
    $response = $e->getResponse();
}
```

## Practice

#### Handling PHP errors ([whoops](https://github.com/filp/whoops))

```bash
composer require middlewares/whoops
```

```php
$router->middleware(new \Middlewares\Whoops());
```

#### Payload ([payload](https://github.com/middlewares/payload))

```bash
composer require middlewares/payload
```

```php
$router->middleware(new \Middlewares\JsonPayload());
$router->middleware(new \Middlewares\UrlEncodePayload());
```

#### Encoding ([encoder](https://github.com/middlewares/encoder))

```bash
composer require middlewares/encoder
```

```php
$router->middleware(new \Middlewares\GzipEncoder());
```

## Awesome PSR-15 Middlewares

> Fully compatible with this repository.

https://github.com/middlewares

## Test run

```bash
php vendor/bin/phpunit
```

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

https://www.php-fig.org/psr/psr-7/<br>
https://www.php-fig.org/psr/psr-15/<br>
https://github.com/middlewares
