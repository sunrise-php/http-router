<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Attributed;

use Sunrise\Http\Router\Annotation as Routing;
use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;
use Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware;

#[Routing\Host('host')]
#[Routing\Prefix('/prefix')]
#[Routing\Postfix('.json')]
#[Routing\Middleware(BlankMiddleware::class)]
#[Routing\Middleware(BlankMiddleware::class)]
#[Routing\Middleware(BlankMiddleware::class)]
final class GroupedAttributedController extends AbstractController
{
    #[Routing\Route('first-from-grouped-attributed-controller', path: '/first')]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    public function first($request)
    {
        return $this->handle($request);
    }

    #[Routing\Route('second-from-grouped-attributed-controller', path: '/second')]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    public function second($request)
    {
        return $this->handle($request);
    }

    #[Routing\Route('third-from-grouped-attributed-controller', path: '/third')]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    #[Routing\Middleware(BlankMiddleware::class)]
    public function third($request)
    {
        return $this->handle($request);
    }
}
