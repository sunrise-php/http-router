<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = function ($request) {
    return (new BlankController)->handle($request);
};

$middleware = function ($request, $handler) {
    return (new BlankMiddleware)->process($request, $handler);
};

$this->get('resolvable-closure-route', '/', $controller, [$middleware]);
