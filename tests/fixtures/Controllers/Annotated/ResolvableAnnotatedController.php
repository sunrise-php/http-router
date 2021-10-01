<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="resolvable-annotated-controller",
 *   path="/",
 *   method="GET",
 *   middlewares={
 *     "Sunrise\Http\Router\Test\Fixture\Middlewares\BlankMiddleware",
 *   },
 * )
 */
final class ResolvableAnnotatedController extends AbstractController
{
}
