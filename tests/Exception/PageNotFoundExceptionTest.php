<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * PageNotFoundExceptionTest
 */
class PageNotFoundExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new PageNotFoundException();

        $this->assertInstanceOf(RouteNotFoundException::class, $exception);
    }
}
