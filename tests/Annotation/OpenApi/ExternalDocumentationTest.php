<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\ExternalDocumentation;
use Sunrise\Http\Router\Annotation\OpenApi\ExternalDocumentationInterface;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * ExternalDocumentationTest
 */
class ExternalDocumentationTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new ExternalDocumentation();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(ExternalDocumentationInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }
}
