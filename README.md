# HTTP Router for PHP 7.2+ based on PSR-7 and PSR-15

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
	$response->getBody()->write('Hello, world!');

	return $response;
});

$request = ServerRequestFactory::fromGlobals();

$response = $router->handle($request);
```

#### Routes

```php
// Register a new route
$route = $router->add(string $id, string $path, callable $action);
// Register a new route that will respond to HEAD requests
$route = $router->head(string $id, string $path, callable $action);
// Register a new route that will respond to GET requests
$route = $router->get(string $id, string $path, callable $action);
// Register a new route that will respond to POST requests
$route = $router->post(string $id, string $path, callable $action);
// Register a new route that will respond to PUT requests
$route = $router->put(string $id, string $path, callable $action);
// Register a new route that will respond to PATCH requests
$route = $router->patch(string $id, string $path, callable $action);
// Register a new route that will respond to DELETE requests
$route = $router->delete(string $id, string $path, callable $action);
// Register a new route that will respond to PURGE requests
$route = $router->purge(string $id, string $path, callable $action);
// Register a new route that will respond to OPTIONS requests
$route = $router->options(string $id, string $path, callable $action);
// Register a new route that will respond to TRACE requests
$route = $router->trace(string $id, string $path, callable $action);
// Register a new route that will respond to CONNECT requests
$route = $router->connect(string $id, string $path, callable $action);
// Register a new route that will respond to safe requests
$route = $router->safe(string $id, string $path, callable $action);
// Register a new route that will respond to any requests
$route = $router->any(string $id, string $path, callable $action);
```

#### Patterns

```php
$route = $router->patch('post.update', '/post/{id}', callable $action);

$route->pattern('id', '\d+');
```

#### Middlewares

```php
// Adds a new middleware to the router
$router
->middleware(new MiddlewareFoo())
->middleware(new MiddlewareBar());

// Adds a new middleware to the route
$route
->middleware(new MiddlewareBaz())
->middleware(new MiddlewareQux());
```

## Awesome PSR-15 Middlewares

> Fully compatible with this repository.

https://github.com/middlewares

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

	// getting the allowed methods...
	$allowedMethods = $e->getAllowedMethods();
}
```

#### Handling other error

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
