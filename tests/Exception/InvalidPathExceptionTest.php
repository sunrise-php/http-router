<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\InvalidPathException;

/**
 * InvalidPathExceptionTest
 */
class InvalidPathExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new InvalidPathException();

        $this->assertInstanceOf(Exception::class, $exception);
    }
}
