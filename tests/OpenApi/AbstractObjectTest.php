<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * AbstractObjectTest
 */
class AbstractObjectTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new class extends AbstractObject {
        };

        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testToArray() : void
    {
        $foo = new class extends AbstractObject {
            public $foo = 'foo';
        };

        $bar = new class extends AbstractObject {
            public $bar = 'bar';
        };

        $baz = new class extends AbstractObject {
        };

        $object = new class ($foo, $bar, $baz) extends AbstractObject {
            protected const IGNORE_FIELDS = [
                'p11',
                'p18',
            ];

            protected const FIELD_ALIASES = [
                'p12' => 'p12a',
                'p19' => 'p19a',
            ];

            private $p01;
            private $p02 = 0;
            private $p03 = [];
            private $p04 = '';
            private $p05 = 'value';

            protected $p06;
            protected $p07 = 0;
            protected $p08 = [];
            protected $p09 = '';
            protected $p10 = 'value';
            protected $p11 = 'value';
            protected $p12 = 'value';

            public $p13;
            public $p14 = 0;
            public $p15 = [];
            public $p16 = '';
            public $p17 = 'value';
            public $p18 = 'value';
            public $p19 = 'value';

            public function __construct($foo, $bar, $baz)
            {
                $this->p20 = $foo;
                $this->p21 = $bar;
                $this->p22 = $baz;
            }
        };

        $this->assertSame([
            'p07' => 0,
            'p08' => [],
            'p09' => '',
            'p10' => 'value',
            'p12a' => 'value',
            'p14' => 0,
            'p15' => [],
            'p16' => '',
            'p17' => 'value',
            'p19a' => 'value',
            'p20' => ['foo' => 'foo'],
            'p21' => ['bar' => 'bar'],
            'p22' => [],
        ], $object->toArray());
    }
}
