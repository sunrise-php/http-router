<?php declare(strict_types=1);

use Sunrise\Http\Router\Test\Fixture\Controllers\BlankController;
use Sunrise\Http\Router\Test\Fixture\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = new ReflectionClass(BlankController::class);
$middleware = new ReflectionClass(BlankMiddleware::class);

$this->get('resolvable-reflection-class-route', '/', $controller, [$middleware]);
