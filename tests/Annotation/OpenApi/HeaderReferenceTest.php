<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractReference;
use Sunrise\Http\Router\Annotation\OpenApi\Header;
use Sunrise\Http\Router\Annotation\OpenApi\HeaderInterface;
use Sunrise\Http\Router\Annotation\OpenApi\HeaderReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * HeaderReferenceTest
 */
class HeaderReferenceTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $reference = new HeaderReference();

        $this->assertInstanceOf(AbstractReference::class, $reference);
        $this->assertInstanceOf(HeaderInterface::class, $reference);
        $this->assertInstanceOf(ObjectInterface::class, $reference);
    }

    /**
     * @return void
     */
    public function testGetAnnotationName() : void
    {
        $reference = new HeaderReference();

        $this->assertSame(Header::class, $reference->getAnnotationName());
    }
}
