<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\InvalidPathException;
use InvalidArgumentException;

/**
 * InvalidPathExceptionTest
 */
class InvalidPathExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new InvalidPathException();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
    }
}
