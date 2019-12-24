<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\Parameter;
use Sunrise\Http\Router\Annotation\OpenApi\Response;
use Sunrise\Http\Router\Annotation\OpenApi\Schema;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\OpenApi\AbstractAnnotationReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * Import functions
 */
use function get_class;
use function sprintf;

/**
 * AbstractAnnotationReferenceTest
 */
class AbstractAnnotationReferenceTest extends TestCase
{
    use Fixture\AwareSimpleAnnotationReader;

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->assertInstanceOf(ObjectInterface::class, $reference);
    }

    /**
     * @return void
     */
    public function testToArrayWithoutReadingAnnotation() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->assertSame(['$ref' => 'undefined'], $reference->toArray());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromClass() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Entity\Pet::class;

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        // an annotation must not be read twice...
        $this->assertSame(
            $reference->getAnnotation($this->createSimpleAnnotationReader()),
            $reference->getAnnotation($this->createSimpleAnnotationReader())
        );

        $this->assertSame(['$ref' => '#/components/schemas/Pet'], $reference->toArray());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromClassThatDoesntContainAnnotation() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Entity\Pet::class;

            public function getAnnotationName() : string
            {
                return Response::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Class %s does not contain the annotation %s',
            $reference->class,
            $reference->getAnnotationName()
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromNonexistentClass() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = 'undefined';

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation %s refers to non-existent class %s',
            get_class($reference),
            $reference->class
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromProperty() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $property = 'limit';

            public function getAnnotationName() : string
            {
                return Parameter::class;
            }
        };

        $reference->getAnnotation($this->createSimpleAnnotationReader());

        $this->assertSame(['$ref' => '#/components/parameters/queryLimit'], $reference->toArray());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromPropertyThatDoesntContainAnnotation() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $property = 'limit';

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Property %s::$%s does not contain the annotation %s',
            $reference->class,
            $reference->property,
            $reference->getAnnotationName()
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromNonexistentProperty() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $property = 'undefined';

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation %s refers to non-existent property %s::$%s',
            get_class($reference),
            $reference->class,
            $reference->property
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromMethod() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $method = 'error';

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $reference->getAnnotation($this->createSimpleAnnotationReader());

        $this->assertSame(['$ref' => '#/components/schemas/Error'], $reference->toArray());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromMethodThatDoesntContainAnnotation() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $method = 'error';

            public function getAnnotationName() : string
            {
                return Response::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Method %s::%s() does not contain the annotation %s',
            $reference->class,
            $reference->method,
            $reference->getAnnotationName()
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }

    /**
     * @return void
     */
    public function testGetAnnotationFromNonexistentMethod() : void
    {
        $reference = new class extends AbstractAnnotationReference
        {
            public $class = Fixture\PetStore\Endpoint::class;
            public $method = 'undefined';

            public function getAnnotationName() : string
            {
                return Schema::class;
            }
        };

        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation %s refers to non-existent method %s::%s()',
            get_class($reference),
            $reference->class,
            $reference->method
        ));

        $reference->getAnnotation($this->createSimpleAnnotationReader());
    }
}
