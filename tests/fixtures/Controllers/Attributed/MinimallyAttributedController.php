<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Attributed;

use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

#[Route(
    name: 'minimally-attributed-controller',
    path: '/',
    method: 'GET',
)]
final class MinimallyAttributedController extends AbstractController
{
}
