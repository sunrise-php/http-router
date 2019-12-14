<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\Annotation\OpenApi\MediaType;
use Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * MediaTypeTest
 */
class MediaTypeTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new MediaType();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(MediaTypeInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }
}
