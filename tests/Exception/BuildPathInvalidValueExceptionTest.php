<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\BuildPathInvalidValueException;
use RuntimeException;

/**
 * BuildPathInvalidValueExceptionTest
 */
class BuildPathInvalidValueExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new BuildPathInvalidValueException();

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
    }
}
