<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = new ReflectionMethod(BlankController::class, '__invoke');
$middleware = new ReflectionMethod(BlankMiddleware::class, '__invoke');

$this->get('resolvable-reflection-method-route', '/', $controller, [$middleware]);