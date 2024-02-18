<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Loader;

use InvalidArgumentException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

use function glob;
use function is_dir;
use function is_file;
use function sprintf;

final class ConfigLoader implements LoaderInterface
{
    /**
     * @var list<string>
     */
    private array $resources = [];

    /**
     * @throws InvalidArgumentException If one of the resources isn't valid.
     */
    public function attach(string ...$resources): void
    {
        foreach ($resources as $resource) {
            if (is_file($resource)) {
                $this->resources[] = $resource;
                continue;
            }

            if (is_dir($resource)) {
                /** @var list<string> $filenames */
                $filenames = glob($resource . '/*.php');
                foreach ($filenames as $filename) {
                    $this->resources[] = $filename;
                }

                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The method %s only accepts file or directory names; ' .
                'however, the resource %s is not one of them.',
                __METHOD__,
                $resource,
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function load(): array
    {
        $collector = new RouteCollector();

        foreach ($this->resources as $resource) {
            (function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require $filename;
            })->call($collector, $resource);
        }

        return $collector->getRoutes();
    }
}
