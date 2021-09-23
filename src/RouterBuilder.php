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

/**
 * RouterBuilder
 *
 * @since 2.9.0
 */
final class RouterBuilder
{

    /**
     * @var null|ContainerInterface
     */
    private $container = null;

    /**
     * @var null|CacheInterface
     */
    private $cache = null;

    /**
     * @var null|array<string, string[]>
     */
    private $hosts = null;

    /**
     * @var null|MiddlewareInterface[]
     */
    private $middlewares = null;

    /**
     * @var null|Loader\CollectableFileLoader
     */
    private $configLoader = null;

    /**
     * @var null|Loader\DescriptorDirectoryLoader
     */
    private $metadataLoader = null;

    /**
     * @param null|ContainerInterface $container
     *
     * @return self
     */
    public function setContainer(?ContainerInterface $container) : self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param null|CacheInterface $cache
     *
     * @return self
     */
    public function setCache(?CacheInterface $cache) : self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param string[] $resources
     *
     * @return self
     */
    public function useConfigLoader(array $resources) : self
    {
        $this->configLoader = new Loader\CollectableFileLoader();
        $this->configLoader->attachArray($resources);

        return $this;
    }

    /**
     * @param string[] $resources
     *
     * @return self
     */
    public function useMetadataLoader(array $resources) : self
    {
        $this->metadataLoader = new Loader\DescriptorDirectoryLoader();
        $this->metadataLoader->attachArray($resources);

        return $this;
    }

    /**
     * @param null|array<string, string[]> $hosts
     *
     * @return self
     */
    public function setHosts(?array $hosts) : self
    {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * @param null|MiddlewareInterface[] $middlewares
     *
     * @return self
     */
    public function setMiddlewares(?array $middlewares) : self
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @return Router
     */
    public function build() : Router
    {
        $router = new Router();

        if (isset($this->configLoader)) {
            $this->configLoader->setContainer($this->container);
            $router->load($this->configLoader);
        }

        if (isset($this->metadataLoader)) {
            $this->metadataLoader->setContainer($this->container);
            $this->metadataLoader->setCache($this->cache);
            $router->load($this->metadataLoader);
        }

        if (!empty($this->hosts)) {
            foreach ($this->hosts as $alias => $hostnames) {
                $router->addHost($alias, ...$hostnames);
            }
        }

        if (!empty($this->middlewares)) {
            $router->addMiddleware(...$this->middlewares);
        }

        return $router;
    }
}
