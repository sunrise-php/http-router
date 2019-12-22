<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\Response;
use Sunrise\Http\Router\Annotation\OpenApi\ResponseInterface;
use Sunrise\Http\Router\Annotation\OpenApi\ResponseReference;
use Sunrise\Http\Router\OpenApi\AbstractAnnotationReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * ResponseReferenceTest
 */
class ResponseReferenceTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $reference = new ResponseReference();

        $this->assertInstanceOf(AbstractAnnotationReference::class, $reference);
        $this->assertInstanceOf(ResponseInterface::class, $reference);
        $this->assertInstanceOf(ObjectInterface::class, $reference);
    }

    /**
     * @return void
     */
    public function testGetAnnotationName() : void
    {
        $reference = new ResponseReference();

        $this->assertSame(Response::class, $reference->getAnnotationName());
    }
}
