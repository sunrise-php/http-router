<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Invalid;

use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="home",
 *   path="/",
 *   methods={"GET"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     {"Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"},
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   }
 * )
 */
class MiddlewaresNotStringable extends BlankRequestHandler
{
}
