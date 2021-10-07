<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = BlankController::class;
$middleware = BlankMiddleware::class;

$this->get('resolvable-class-name-route', '/', $controller, [$middleware]);
