<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\InvalidDescriptorException;

/**
 * InvalidDescriptorExceptionTest
 */
class InvalidDescriptorExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new InvalidDescriptorException();

        $this->assertInstanceOf(Exception::class, $exception);
    }
}