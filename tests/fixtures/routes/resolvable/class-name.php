<?php declare(strict_types=1);

use Sunrise\Http\Router\Test\Fixture\Controllers\BlankController;
use Sunrise\Http\Router\Test\Fixture\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = BlankController::class;
$middleware = BlankMiddleware::class;

$this->get('resolvable-class-name-route', '/', $controller, [$middleware]);
