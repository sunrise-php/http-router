<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures;

use Psr\SimpleCache\CacheInterface;

trait CacheAwareTrait
{

    /**
     * @return CacheInterface
     */
    private function getCache() : CacheInterface
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->storage = [];

        $cache->method('get')->will($this->returnCallback(function ($key) use ($cache) {
            return $cache->storage[$key] ?? null;
        }));

        $cache->method('has')->will($this->returnCallback(function ($key) use ($cache) {
            return isset($cache->storage[$key]);
        }));

        $cache->method('set')->will($this->returnCallback(function ($key, $value) use ($cache) {
            $cache->storage[$key] = $value;
        }));

        return $cache;
    }
}
