<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="minimally-annotated-controller",
 *   path="/",
 *   method="GET",
 * )
 */
final class MinimallyAnnotatedController extends AbstractController
{
}
