<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{

    /**
     * @return ContainerInterface
     */
    private function getContainer(array $definitions = []) : ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->storage = $definitions;

        $container->method('get')->will($this->returnCallback(function ($key) use ($container) {
            return $container->storage[$key] ?? null;
        }));

        $container->method('has')->will($this->returnCallback(function ($key) use ($container) {
            return isset($container->storage[$key]);
        }));

        return $container;
    }
}
