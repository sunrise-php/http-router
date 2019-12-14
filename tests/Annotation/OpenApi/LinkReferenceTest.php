<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractReference;
use Sunrise\Http\Router\Annotation\OpenApi\Link;
use Sunrise\Http\Router\Annotation\OpenApi\LinkInterface;
use Sunrise\Http\Router\Annotation\OpenApi\LinkReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * LinkReferenceTest
 */
class LinkReferenceTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $reference = new LinkReference();

        $this->assertInstanceOf(AbstractReference::class, $reference);
        $this->assertInstanceOf(LinkInterface::class, $reference);
        $this->assertInstanceOf(ObjectInterface::class, $reference);
    }

    /**
     * @return void
     */
    public function testGetAnnotationName() : void
    {
        $reference = new LinkReference();

        $this->assertSame(Link::class, $reference->getAnnotationName());
    }
}
