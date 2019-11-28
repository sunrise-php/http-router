### HTTP router with annotations support for PHP 7.1+ based on PSR-7 and PSR-15

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-router/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-router/downloads?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-router/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-router)
[![License](https://poser.pugx.org/sunrise/http-router/license?format=flat)](https://packagist.org/packages/sunrise/http-router)

---

### Installation

```bash
composer require 'sunrise/http-router:^2.0'
```

### Quick start

#### Loading routes from configs

```php
use Sunrise\Http\Router\Loader\CollectableFileLoader;
use Sunrise\Http\Router\Router;

$loader = new CollectableFileLoader();
$loader->attach('routes/api.php');
$loader->attach('routes/admin.php');
$loader->attach('routes/public.php');

$router = new Router();
$router->load($loader);

$response = $router->handle($request);
```

#### Loading routes from annotations

```php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sunrise\Http\Router\Loader\AnnotationDirectoryLoader;
use Sunrise\Http\Router\Router;

AnnotationRegistry::registerLoader('class_exists');

$loader = new AnnotationDirectoryLoader();
$loader->attach('src/Http/RequestHandler');

$router = new Router();
$router->load($loader);

$response = $router->handle($request);
```

#### Without loading strategy

```php
use App\Http\RequestHandler\HomeRequestHandler;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\Router;

$router = new Router();

$router->get('home', '/', new HomeRequestHandler());

$response = $router->handle($request);
```
