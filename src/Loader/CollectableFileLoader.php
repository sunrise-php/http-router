<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\InvalidLoadResourceException;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;

/**
 * Import functions
 */
use function is_file;
use function sprintf;

/**
 * CollectableFileLoader
 */
class CollectableFileLoader implements LoaderInterface
{

    /**
     * @var string[]
     */
    private $resources = [];

    /**
     * {@inheritDoc}
     */
    public function attach($resource) : void
    {
        if (!is_file($resource)) {
            throw new InvalidLoadResourceException(
                sprintf('The "%s" resource not found.', $resource)
            );
        }

        $this->resources[] = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function load() : RouteCollectionInterface
    {
        $collect = new RouteCollector();

        foreach ($this->resources as $resource) {
            (function () use ($resource) {
                require $resource;
            })->call($collect);
        }

        return $collect->getCollection();
    }
}
