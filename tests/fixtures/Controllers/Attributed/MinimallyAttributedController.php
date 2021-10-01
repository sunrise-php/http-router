<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Attributed;

use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

#[Route(
    name: 'minimally-attributed-controller',
    path: '/',
    method: 'GET',
)]
final class MinimallyAttributedController extends AbstractController
{
}
