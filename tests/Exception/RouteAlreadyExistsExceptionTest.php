<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\AbstractException;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;

/**
 * RouteAlreadyExistsExceptionTest
 */
class RouteAlreadyExistsExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new RouteAlreadyExistsException();

        $this->assertInstanceOf(AbstractException::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new RouteAlreadyExistsException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new RouteAlreadyExistsException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }
}
