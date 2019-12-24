<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\Schema;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\AbstractAnnotationReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * AbstractAnnotationTest
 */
class AbstractAnnotationTest extends TestCase
{
    use Fixture\AwareSimpleAnnotationReader;

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $this->assertInstanceOf(ObjectInterface::class, new class extends AbstractAnnotation {
        });
    }

    /**
     * @return void
     */
    public function testGetReferencedObjects() : void
    {
        $annotation = new class extends AbstractAnnotation
        {
            public $child;
            public $reference;

            public function __construct()
            {
                $this->child = clone $this;

                $this->child->reference = new class extends AbstractAnnotationReference
                {
                    public $class = Fixture\PetStore\Entity\Pet::class;

                    public function getAnnotationName() : string
                    {
                        return Schema::class;
                    }
                };
            }
        };

        $expected = $this->createSimpleAnnotationReader()
        ->getClassAnnotation(new \ReflectionClass(Fixture\PetStore\Entity\Pet::class), Schema::class)
        ->toArray();

        $referencedObjects = $annotation->getReferencedObjects($this->createSimpleAnnotationReader());

        $this->assertSame($expected, $referencedObjects[0]->toArray());
    }
}
