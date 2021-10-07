<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = new ReflectionClass(BlankController::class);
$middleware = new ReflectionClass(BlankMiddleware::class);

$this->get('resolvable-reflection-class-route', '/', $controller, [$middleware]);
