<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = [BlankController::class, '__invoke'];
$middleware = [BlankMiddleware::class, '__invoke'];

$this->get('resolvable-class-method-name-route', '/', $controller, [$middleware]);
