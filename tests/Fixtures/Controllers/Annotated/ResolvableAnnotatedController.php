<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

/**
 * @Route(
 *   name="resolvable-annotated-controller",
 *   path="/",
 *   method="GET",
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware",
 *   },
 * )
 */
final class ResolvableAnnotatedController extends AbstractController
{
}
