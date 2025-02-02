<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Mock;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

final class CacheMock implements CacheInterface
{
    private array $store = [];

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $this->store[$key] = $value;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        unset($this->store[$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->store = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->store[$key] ?? $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->store[$key] = $value;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->store[$key]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return isset($this->store[$key]);
    }
}
