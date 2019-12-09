<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractReference;
use Sunrise\Http\Router\Annotation\OpenApi\AnnotationInterface;

/**
 * AbstractReferenceTest
 */
class AbstractReferenceTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $reference = $this->createMock(AbstractReference::class);

        $this->assertInstanceOf(AnnotationInterface::class, $reference);
    }

    /**
     * @return void
     */
    public function testGetComponentPath() : void
    {
        $reference = new class extends AbstractReference
        {
            public $name = 'reference';

            public function getAnnotationName() : string
            {
                return 'annotation';
            }

            public function getComponentName() : string
            {
                return 'component';
            }
        };

        $this->assertSame('#/components/component/reference', $reference->getComponentPath());
    }

    /**
     * @return void
     */
    public function testToArray() : void
    {
        $reference = new class extends AbstractReference
        {
            public function getAnnotationName() : string
            {
                return 'annotation';
            }

            public function getComponentName() : string
            {
                return 'component';
            }
        };

        $this->assertSame(['$ref' => $reference], $reference->toArray());
    }
}
