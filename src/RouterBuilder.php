<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * RouterBuilder
 *
 * @since 2.9.0
 */
final class RouterBuilder
{

    /**
     * @var EventDispatcherInterface|null
     *
     * @since 2.14.0
     */
    private $eventDispatcher = null;

    /**
     * @var ContainerInterface|null
     */
    private $container = null;

    /**
     * @var CacheInterface|null
     */
    private $cache = null;

    /**
     * @var string|null
     */
    private $cacheKey = null;

    /**
     * @var array<string, string>|null
     */
    private $patterns = null;

    /**
     * @var array<string, string[]>|null
     */
    private $hosts = null;

    /**
     * @var MiddlewareInterface[]|null
     */
    private $middlewares = null;

    /**
     * @var Loader\ConfigLoader|null
     */
    private $configLoader = null;

    /**
     * @var Loader\DescriptorLoader|null
     */
    private $descriptorLoader = null;

    /**
     * Sets the given event dispatcher to the builder
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     *
     * @return self
     *
     * @since 2.14.0
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher) : self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Sets the given container to the builder
     *
     * @param ContainerInterface|null $container
     *
     * @return self
     */
    public function setContainer(?ContainerInterface $container) : self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Sets the given cache to the builder
     *
     * @param CacheInterface|null $cache
     *
     * @return self
     */
    public function setCache(?CacheInterface $cache) : self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Sets the given cache key to the builder
     *
     * @param string|null $cacheKey
     *
     * @return self
     *
     * @since 2.10.0
     */
    public function setCacheKey(?string $cacheKey) : self
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * Uses the config loader when building
     *
     * @param string[] $resources
     *
     * @return self
     */
    public function useConfigLoader(array $resources) : self
    {
        $this->configLoader = new Loader\ConfigLoader();
        $this->configLoader->attachArray($resources);

        return $this;
    }

    /**
     * Uses the descriptor loader when building
     *
     * @param string[] $resources
     *
     * @return self
     */
    public function useDescriptorLoader(array $resources) : self
    {
        $this->descriptorLoader = new Loader\DescriptorLoader();
        $this->descriptorLoader->attachArray($resources);

        return $this;
    }

    /**
     * Uses the metadata loader when building
     *
     * Alias to the useDescriptorLoader method.
     *
     * @param string[] $resources
     *
     * @return self
     */
    public function useMetadataLoader(array $resources) : self
    {
        $this->useDescriptorLoader($resources);

        return $this;
    }

    /**
     * Sets the given patterns to the builder
     *
     * @param array<string, string>|null $patterns
     *
     * @return self
     *
     * @since 2.11.0
     */
    public function setPatterns(?array $patterns) : self
    {
        $this->patterns = $patterns;

        return $this;
    }

    /**
     * Sets the given hosts to the builder
     *
     * @param array<string, string[]>|null $hosts
     *
     * @return self
     */
    public function setHosts(?array $hosts) : self
    {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * Sets the given middlewares to the builder
     *
     * @param MiddlewareInterface[]|null $middlewares
     *
     * @return self
     */
    public function setMiddlewares(?array $middlewares) : self
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * Builds the router
     *
     * @return Router
     */
    public function build() : Router
    {
        $router = new Router();

        if (isset($this->eventDispatcher)) {
            $router->setEventDispatcher($this->eventDispatcher);
        }

        if (isset($this->configLoader)) {
            $this->configLoader->setContainer($this->container);
            $router->load($this->configLoader);
        }

        if (isset($this->descriptorLoader)) {
            $this->descriptorLoader->setContainer($this->container);
            $this->descriptorLoader->setCache($this->cache);
            $this->descriptorLoader->setCacheKey($this->cacheKey);
            $router->load($this->descriptorLoader);
        }

        if (!empty($this->patterns)) {
            $router->addPatterns($this->patterns);
        }

        if (!empty($this->hosts)) {
            $router->addHosts($this->hosts);
        }

        if (!empty($this->middlewares)) {
            $router->addMiddleware(...$this->middlewares);
        }

        return $router;
    }
}
