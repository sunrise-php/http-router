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

use FilesystemIterator;
use Generator;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

use function class_exists;
use function get_declared_classes;
use function hash;
use function is_dir;
use function is_string;
use function iterator_to_array;
use function sprintf;
use function usort;

/**
 * DescriptorLoader
 */
final class DescriptorLoader implements LoaderInterface
{

    /**
     * List of classes or directories
     *
     * @var list<string>
     */
    private array $resources = [];

    /**
     * @var RouteCollectionFactoryInterface
     */
    private RouteCollectionFactoryInterface $collectionFactory;

    /**
     * @var RouteFactoryInterface
     */
    private RouteFactoryInterface $routeFactory;

    /**
     * @var ReferenceResolverInterface
     */
    private ReferenceResolverInterface $referenceResolver;

    /**
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache;

    /**
     * @var string|null
     */
    private ?string $cacheKey = null;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param RouteFactoryInterface|null $routeFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     * @param CacheInterface|null $cache
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null,
        ?CacheInterface $cache = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver();

        $this->cache = $cache;
    }

    /**
     * Sets the given cache key to the loader
     *
     * @param string|null $cacheKey
     *
     * @return void
     *
     * @since 2.10.0
     */
    public function setCacheKey(?string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Gets the loader cache key
     *
     * @return string
     *
     * @since 2.10.0
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey ??= hash('md5', $this::class);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If the resource isn't valid.
     */
    public function attach(mixed $resource): void
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'The descriptor route loader only handles string resources.'
            );
        }

        if (!class_exists($resource) && !is_dir($resource)) {
            throw new InvalidArgumentException(sprintf(
                'The descriptor route loader only handles class names or directory paths, ' .
                'however the given resource "%s" is not one of them.',
                $resource,
            ));
        }

        $this->resources[] = $resource;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If one of the given resources isn't valid.
     */
    public function attachArray(array $resources): void
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * @inheritDoc
     */
    public function load(): RouteCollectionInterface
    {
        $routes = $this->collectionFactory->createCollection();
        $descriptors = $this->getDescriptors();
        foreach ($descriptors as $descriptor) {
            $route = $this->routeFactory->createRoute(
                $descriptor->name,
                $descriptor->path,
                $descriptor->methods,
                $this->referenceResolver->resolveRequestHandler($descriptor->holder),
                [...$this->referenceResolver->resolveMiddlewares($descriptor->middlewares)],
                $descriptor->attributes,
            );

            $route->setConsumesMediaTypes(...$descriptor->consumes);
            $route->setProducesMediaTypes(...$descriptor->produces);
            $route->setSummary($descriptor->summary);
            $route->setDescription($descriptor->description);
            $route->setTags(...$descriptor->tags);
            $route->setDeprecation($descriptor->isDeprecated);

            $routes->add($route);
        }

        return $routes;
    }

    /**
     * Gets descriptors from the cache if they are stored in it,
     * otherwise collects them from the loader resources,
     * then tries to cache and return them.
     *
     * @return list<Route>
     */
    private function getDescriptors(): array
    {
        $cacheKey = $this->getCacheKey();

        if (isset($this->cache) && $this->cache->has($cacheKey)) {
            /** @var list<Route> */
            return $this->cache->get($cacheKey);
        }

        $result = [];
        foreach ($this->resources as $resource) {
            $descriptors = $this->getResourceDescriptors($resource);
            foreach ($descriptors as $descriptor) {
                $result[] = $descriptor;
            }
        }

        usort($result, static fn(Route $a, Route $b): int => $b->priority <=> $a->priority);

        if (isset($this->cache)) {
            $this->cache->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Gets descriptors from the given resource
     *
     * @param string $resource
     *
     * @return Generator<int, Route>
     */
    private function getResourceDescriptors(string $resource): Generator
    {
        if (class_exists($resource)) {
            return yield from $this->getClassDescriptors(new ReflectionClass($resource));
        }

        if (is_dir($resource)) {
            foreach ($this->getDirectoryClasses($resource) as $class) {
                yield from $this->getClassDescriptors($class);
            }
        }
    }

    /**
     * Gets descriptors from the given class
     *
     * @param ReflectionClass $class
     *
     * @return Generator<int, Route>
     */
    private function getClassDescriptors(ReflectionClass $class): Generator
    {
        if (!$class->isInstantiable()) {
            return;
        }

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            /** @var list<ReflectionAttribute<Route>> $annotations */
            $annotations = $class->getAttributes(Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0]->newInstance();
                $descriptor->holder = $class->getName();
                $this->supplementDescriptorFromParentClasses($descriptor, $class);
                $this->supplementDescriptorFromClassOrMethod($descriptor, $class);
                yield $descriptor;
            }
        }

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Statical methods must be ignored...
            if ($method->isStatic()) {
                continue;
            }

            /** @var list<ReflectionAttribute<Route>> $annotations */
            $annotations = $method->getAttributes(Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0]->newInstance();
                $descriptor->holder = [$class->getName(), $method->getName()];
                $this->supplementDescriptorFromParentClasses($descriptor, $class);
                $this->supplementDescriptorFromClassOrMethod($descriptor, $class);
                $this->supplementDescriptorFromClassOrMethod($descriptor, $method);
                yield $descriptor;
            }
        }
    }

    /**
     * Supplements the given descriptor from parent classes of the given class
     *
     * @param Route $descriptor
     * @param ReflectionClass $child
     *
     * @return void
     */
    private function supplementDescriptorFromParentClasses(Route $descriptor, ReflectionClass $child): void
    {
        $parents = [];
        while ($child = $child->getParentClass()) {
            $parents = [$child, ...$parents];
        }

        foreach ($parents as $parent) {
            $this->supplementDescriptorFromClassOrMethod($descriptor, $parent);
        }
    }

    /**
     * Supplements the given descriptor from the given class or method
     *
     * @param Route $descriptor
     * @param ReflectionClass|ReflectionMethod $holder
     *
     * @return void
     */
    // phpcs:ignore Generic.Files.LineLength
    private function supplementDescriptorFromClassOrMethod(Route $descriptor, ReflectionClass|ReflectionMethod $holder): void
    {
        /** @var list<ReflectionAttribute<Prefix>> $annotations */
        $annotations = $holder->getAttributes(Prefix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->path = $annotation->value . $descriptor->path;
        }

        /** @var list<ReflectionAttribute<Postfix>> $annotations */
        $annotations = $holder->getAttributes(Postfix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->path .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Method>> $annotations */
        $annotations = $holder->getAttributes(Method::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->methods[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Consumes>> $annotations */
        $annotations = $holder->getAttributes(Consumes::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->consumes[] = new MediaType($annotation->type, $annotation->subtype);
        }

        /** @var list<ReflectionAttribute<Produces>> $annotations */
        $annotations = $holder->getAttributes(Produces::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->produces[] = new MediaType($annotation->type, $annotation->subtype, $annotation->parameters);
        }

        /** @var list<ReflectionAttribute<Middleware>> $annotations */
        $annotations = $holder->getAttributes(Middleware::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->middlewares[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Summary>> $annotations */
        $annotations = $holder->getAttributes(Summary::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            /** @psalm-suppress PossiblyNullOperand */
            $descriptor->summary .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Description>> $annotations */
        $annotations = $holder->getAttributes(Description::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            /** @psalm-suppress PossiblyNullOperand */
            $descriptor->description .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Tag>> $annotations */
        $annotations = $holder->getAttributes(Tag::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->tags[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Deprecated>> $annotations */
        $annotations = $holder->getAttributes(Deprecated::class);
        if (isset($annotations[0])) {
            $descriptor->isDeprecated = true;
        }
    }

    /**
     * Scans the given directory and returns the found classes
     *
     * @param string $dirname
     *
     * @return Generator<int, ReflectionClass>
     */
    private function getDirectoryClasses(string $dirname): Generator
    {
        /** @var array<string, string> $filenames */
        // phpcs:ignore Generic.Files.LineLength
        $filenames = iterator_to_array(new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_PATHNAME)), '/\.php$/'));

        foreach ($filenames as $filename) {
            (static function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require_once $filename;
            })($filename);
        }

        foreach (get_declared_classes() as $className) {
            $classReflection = new ReflectionClass($className);
            if (isset($filenames[$classReflection->getFileName()])) {
                yield $classReflection;
            }
        }
    }
}
