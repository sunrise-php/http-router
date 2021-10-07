<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

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
