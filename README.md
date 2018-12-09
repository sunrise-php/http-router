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

## How to use

```php
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\ServerRequest\ServerRequestFactory;
use Sunrise\Stream\StreamFactory;

$router = new Router();

$router->middleware(new MiddlewareFoo());
$router->middleware(new MiddlewareBar());
$router->middleware(new MiddlewareBaz());

$router->get('home', '/', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream('Welcome'));
})
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

$router->post('post.create', '/post', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream('Create a post'));
})
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

$router->patch('post.update', '/post/{id}', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream(
			\sprintf('Update the post #%d', $request->getAttribute('id'))));
})
->pattern('id', '\d+')
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

$router->delete('post.delete', '/post/{id}', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream(
			\sprintf('Delete the post #%d', $request->getAttribute('id'))));
})
->pattern('id', '\d+')
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

$router->get('post.read', '/post/{id}', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream(
			\sprintf('Read the post #%d', $request->getAttribute('id'))));
})
->pattern('id', '\d+')
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

$router->get('post.all', '/post', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream('All posts'));
})
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

// Custom HTTP methods
$router->add('test', '/test', function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
	return $response->withBody(
		(new StreamFactory)->createStream('Test'));
})
->method(RequestMethodInterface::METHOD_HEAD)
->method(RequestMethodInterface::METHOD_GET)
->method(RequestMethodInterface::METHOD_POST)
->method(RequestMethodInterface::METHOD_PUT)
->method(RequestMethodInterface::METHOD_PATCH)
->method(RequestMethodInterface::METHOD_DELETE)
->method(RequestMethodInterface::METHOD_PURGE)
->method(RequestMethodInterface::METHOD_OPTIONS)
->method(RequestMethodInterface::METHOD_TRACE)
->method(RequestMethodInterface::METHOD_CONNECT)
->middleware(new MiddlewareQux())
->middleware(new MiddlewareQuxx());

// All methods
// $router->head(...);
// $router->get(...);
// $router->post(...);
// $router->put(...);
// $router->patch(...);
// $router->delete(...);
// $router->purge(...);
// $router->options(...);
// $router->trace(...);
// $router->connect(...);
// $router->safe(...);
// $router->any(...);

// Run router (handle the given request)
try {
	$response = $router->handle(ServerRequestFactory::fromGlobals());
} catch (RouteNotFoundException $e) {
	$response = (new ResponseFactory)
	->createResponse(404);
} catch (MethodNotAllowedException $e) {
	$response = (new ResponseFactory)
	->createResponse(405)
	->withHeader('allow', implode(', ', $e->getAllowedMethods()));
}

// Send the response
print_r($response);
```

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

https://www.php-fig.org/psr/psr-7/<br>
https://www.php-fig.org/psr/psr-15/
