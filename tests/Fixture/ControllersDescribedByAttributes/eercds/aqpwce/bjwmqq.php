<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'bjwmqq', path: '/bjwmqq', methods: ['GET'], priority: 7)]
final class bjwmqq extends BlankRequestHandler
{
}
