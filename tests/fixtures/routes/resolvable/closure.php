<?php declare(strict_types=1);

use Sunrise\Http\Router\Test\Fixture\Controllers\BlankController;
use Sunrise\Http\Router\Test\Fixture\Middlewares\BlankMiddleware;

/** @var $this Sunrise\Http\Router\RouteCollector */

$controller = function ($request) {
    return (new BlankController)->handle($request);
};

$middleware = function ($request, $handler) {
    return (new BlankMiddleware)->process($request, $handler);
};

$this->get('resolvable-closure-route', '/', $controller, [$middleware]);
