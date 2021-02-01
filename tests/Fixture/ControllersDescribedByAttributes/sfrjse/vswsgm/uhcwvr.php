<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

// mus be ignored...
#[Route(name: 'uhcwvr', path: '/uhcwvr', methods: ['GET'], priority: 10)]
final class uhcwvr
{
}
