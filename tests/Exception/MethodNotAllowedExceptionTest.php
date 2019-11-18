<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use RuntimeException;

/**
 * MethodNotAllowedExceptionTest
 */
class MethodNotAllowedExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new MethodNotAllowedException([]);

        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /**
     * @return void
     */
    public function testGetAllowedMethods() : void
    {
        $exception = new MethodNotAllowedException(['foo']);

        $this->assertSame(['foo'], $exception->getAllowedMethods());
    }
}
