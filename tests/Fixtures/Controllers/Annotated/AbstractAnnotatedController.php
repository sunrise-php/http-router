<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

/**
 * @Route("abstract-annotated-controller", path="/")
 */
abstract class AbstractAnnotatedController extends AbstractController
{
}
