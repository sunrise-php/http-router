<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\MissingAttributeValueException;
use RuntimeException;

/**
 * MissingAttributeValueExceptionTest
 */
class MissingAttributeValueExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new MissingAttributeValueException();

        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}
