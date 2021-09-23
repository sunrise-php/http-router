<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ArgumentException;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

/**
 * ArgumentExceptionTest
 */
class ArgumentExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new ArgumentException();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
