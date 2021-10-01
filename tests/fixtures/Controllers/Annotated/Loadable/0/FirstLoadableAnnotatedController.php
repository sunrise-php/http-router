<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated\Loadable;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="first-loadable-annotated-controller",
 *   path="/",
 *   method="GET",
 * )
 */
final class FirstLoadableAnnotatedController extends AbstractController
{
}
