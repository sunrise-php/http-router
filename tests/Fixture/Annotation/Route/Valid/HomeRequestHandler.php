<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="home",
 *   path="/",
 *   methods={"HEAD", "GET"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   },
 *   attributes={
 *     "foo": "bar",
 *     "bar": "baz"
 *   },
 *   priority=1000
 * )
 */
class HomeRequestHandler extends BlankRequestHandler
{
}
