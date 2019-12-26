<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Containerable;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="foo",
 *   path="/foo",
 *   methods={"GET"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   }
 * )
 */
class FooRequestHandler extends BlankRequestHandler
{
}
