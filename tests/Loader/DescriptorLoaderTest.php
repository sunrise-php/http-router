<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\Api\PageController;

final class DescriptorLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $router = new Router(
            loaders: [
                new DescriptorLoader([
                    PageController::class,
                ]),
            ],
        );

        $route = $router->getRoute('api.pages.create');
        $this->assertSame('/api/pages', $route->getPath());
        $this->assertSame([PageController::class, 'createPage'], $route->getRequestHandler());
        $this->assertSame(['POST'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Creates a new page', $route->getSummary());
        $expectedPattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedPattern, $route->getPattern());
    }

    public function testClearCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $descriptorLoader = new DescriptorLoader([], $cache);
        $cache->expects(self::once())->method('delete')->with(DescriptorLoader::DESCRIPTORS_CACHE_KEY);
        $descriptorLoader->clearCache();
    }
}
