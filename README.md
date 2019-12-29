## HTTP router for PHP 7.1+ based on PSR-7 and PSR-15<br>with support for annotations and OpenApi

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

---

## Installation via composer

```bash
composer require 'sunrise/http-router:^2.0'
```

## Examples of using

#### Strategy loading routes from configs

```php
use Sunrise\Http\Router\Loader\CollectableFileLoader;
use Sunrise\Http\Router\Router;

$loader = new CollectableFileLoader();
$loader->attach('routes/api.php');
$loader->attach('routes/admin.php');
$loader->attach('routes/public.php');

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
$loader->attach('src/Http/RequestHandler');

$router = new Router();
$router->load($loader);

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

#### Without loading strategy

```php
use App\Http\RequestHandler\HomeRequestHandler;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\Router;

$collector = new RouteCollector();
$collector->get('home', '/', new HomeRequestHandler());

$router = new Router();
$router->addRoute(...$collector->getCollection()->all());

// if the router is used as a request handler
$response = $router->handle($request);

// if the router is used as middleware
$response = $router->process($request, $handler);
```

## Generation documentation for OAS (Swagger)

```bash
composer require 'sunrise/http-router-openapi'
```

```php
use Sunrise\Http\Router\OpenApi\Object\Info;
use Sunrise\Http\Router\OpenApi\OpenApi;

$openApi = new OpenApi(new Info('0.0.1', 'API'));

$openApi->addRoute(...$router->getRoutes());

$openApi->toArray();
```
