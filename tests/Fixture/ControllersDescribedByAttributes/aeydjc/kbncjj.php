<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'kbncjj', path: '/kbncjj', methods: ['GET'])]
final class kbncjj extends BlankRequestHandler
{
}
