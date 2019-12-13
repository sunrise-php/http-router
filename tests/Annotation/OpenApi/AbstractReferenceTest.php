<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractReference;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

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

        $this->assertInstanceOf(ObjectInterface::class, $reference);
    }
}
