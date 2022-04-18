<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

/**
 * @Route(
 *   name="maximally-annotated-controller",
 *   host="local",
 *   path="/",
 *   method="GET",
 *   methods={"HEAD"},
 *   middlewares={
 *     Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware::class,
 *   },
 *   attributes={
 *     "foo": "bar",
 *   },
 *   summary="Lorem ipsum",
 *   description="Lorem ipsum dolor sit amet",
 *   tags={"foo", "bar"},
 * )
 */
final class MaximallyAnnotatedController extends AbstractController
{
}
