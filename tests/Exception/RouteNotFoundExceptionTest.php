<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use RuntimeException;

/**
 * RouteNotFoundExceptionTest
 */
class RouteNotFoundExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new RouteNotFoundException();

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
    }
}
