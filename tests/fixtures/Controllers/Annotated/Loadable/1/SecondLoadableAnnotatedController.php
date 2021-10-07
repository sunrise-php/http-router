<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated\Loadable;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

/**
 * @Route(
 *   name="second-loadable-annotated-controller",
 *   path="/",
 *   method="GET",
 * )
 */
final class SecondLoadableAnnotatedController extends AbstractController
{
}
