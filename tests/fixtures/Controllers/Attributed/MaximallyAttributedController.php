<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Attributed;

use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;
use Sunrise\Http\Router\Test\Fixture\Middlewares\BlankMiddleware;

#[Route(
    name: 'maximally-attributed-controller',
    host: 'local',
    path: '/',
    method: 'GET',
    methods: ['HEAD'],
    middlewares: [
        BlankMiddleware::class,
    ],
    attributes: [
        'foo' => 'bar',
    ],
    summary: 'Lorem ipsum',
    description: 'Lorem ipsum dolor sit amet',
    tags: ['foo', 'bar'],
)]
final class MaximallyAttributedController extends AbstractController
{
}
