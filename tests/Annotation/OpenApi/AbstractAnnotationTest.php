<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * AbstractAnnotationTest
 */
class AbstractAnnotationTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $annotation = $this->createMock(AbstractAnnotation::class);

        $this->assertInstanceOf(ObjectInterface::class, $annotation);
    }

    /**
     * @return void
     */
    public function testToArray() : void
    {
        $annotation = new class extends AbstractAnnotation {
        };

        $testAnnotation = new class extends AbstractAnnotation {
            public $field = 'value';
        };

        // this fields must be skipped...
        $annotation->foo = null;
        $annotation->bar = [];

        $annotation->baz = 0;
        $annotation->qux = '';
        $annotation->quux = $testAnnotation;
        $annotation->quuux = [
            'param' => $testAnnotation,
        ];

        $expected = [
            'bar' => [],
            'baz' => 0,
            'qux' => '',
            'quux' => [
                'field' => 'value',
            ],
            'quuux' => [
                'param' => [
                    'field' => 'value',
                ],
            ],
        ];

        $this->assertSame($expected, $annotation->toArray());
    }
}
