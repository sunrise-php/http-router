<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankMiddleware;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'zfmamx', host: '127.0.0.1', path: '/zfmamx', methods: ['GET'], middlewares: [BlankMiddleware::class], attributes: ['foo' => 'bar'], summary: 'foo', description: 'bar', tags: ['foo', 'bar'], priority: 2)]
final class zfmamx extends BlankRequestHandler
{
}
