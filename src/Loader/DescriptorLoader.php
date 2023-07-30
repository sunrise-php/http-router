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
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;
use Sunrise\Http\Router\Annotation\Consume;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Host;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Produce;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolver\DependencyInjectionParameterResolver;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolutionerInterface;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;
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
     * @var ParameterResolutionerInterface|null
     */
    private ?ParameterResolutionerInterface $parameterResolutioner;

    /**
     * @var ResponseResolutionerInterface|null
     */
    private ?ResponseResolutionerInterface $responseResolutioner;

    /**
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache;

    /**
     * @var non-empty-string|null
     */
    private ?string $cacheKey = null;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param RouteFactoryInterface|null $routeFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     * @param ParameterResolutionerInterface|null $parameterResolutioner
     * @param ResponseResolutionerInterface|null $responseResolutioner
     * @param CacheInterface|null $cache
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null,
        ?ParameterResolutionerInterface $parameterResolutioner = null,
        ?ResponseResolutionerInterface $responseResolutioner = null,
        ?CacheInterface $cache = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;

        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver(
            $this->parameterResolutioner ??= new ParameterResolutioner(),
            $this->responseResolutioner ??= new ResponseResolutioner(),
        );

        $this->cache = $cache;
    }

    /**
     * Sets the given container to the loader
     *
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a parameter resolutioner has not been set.
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->addParameterResolver(new DependencyInjectionParameterResolver($container));
    }

    /**
     * Adds the given parameter resolver(s) to the parameter resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a parameter resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The descriptor route loader cannot accept parameter resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a parameter resolutioner has not been set.'
            );
        }

        $this->parameterResolutioner->addResolver(...$resolvers);
    }

    /**
     * Adds the given response resolver(s) to the response resolutioner
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a response resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        if (!isset($this->responseResolutioner)) {
            throw new LogicException(
                'The descriptor route loader cannot accept response resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a response resolutioner has not been set.'
            );
        }

        $this->responseResolutioner->addResolver(...$resolvers);
    }

    /**
     * Sets the given cache to the loader
     *
     * @param CacheInterface|null $cache
     *
     * @return void
     */
    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Gets the loader cache
     *
     * @return CacheInterface|null
     */
    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Sets the given cache key to the loader
     *
     * @param non-empty-string|null $cacheKey
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
     * @return non-empty-string
     *
     * @since 2.10.0
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey ??= hash('md5', __METHOD__);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     *         If the resource isn't valid.
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
     * @throws InvalidArgumentException
     *         If one of the given resources isn't valid.
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
                $this->referenceResolver->resolveMiddlewares($descriptor->middlewares),
                $descriptor->attributes,
            );

            $route->setHost($descriptor->host);
            $route->setConsumedMediaTypes(...$descriptor->consumes);
            $route->setProducedMediaTypes(...$descriptor->produces);
            $route->setSummary($descriptor->summary);
            $route->setDescription($descriptor->description);
            $route->setTags(...$descriptor->tags);

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
     * @return iterable<Route>
     */
    private function getResourceDescriptors(string $resource): iterable
    {
        if (class_exists($resource)) {
            yield from $this->getClassDescriptors(new ReflectionClass($resource));
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
     * @return iterable<Route>
     */
    private function getClassDescriptors(ReflectionClass $class): iterable
    {
        if (!$class->isInstantiable()) {
            return;
        }

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            $annotations = $this->getAnnotations(Route::class, $class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = $class->getName();
                $this->supplementDescriptor($descriptor, $class);
                yield $descriptor;
            }
        }

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Statical methods must be ignored...
            if ($method->isStatic()) {
                continue;
            }

            $annotations = $this->getAnnotations(Route::class, $method);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = [$class->getName(), $method->getName()];
                $this->supplementDescriptor($descriptor, $class);
                $this->supplementDescriptor($descriptor, $method);
                yield $descriptor;
            }
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
    private function supplementDescriptor(Route $descriptor, ReflectionClass|ReflectionMethod $holder): void
    {
        $annotations = $this->getAnnotations(Host::class, $holder);
        if (isset($annotations[0])) {
            $descriptor->host = $annotations[0]->value;
        }

        $annotations = $this->getAnnotations(Prefix::class, $holder);
        if (isset($annotations[0])) {
            $descriptor->path = $annotations[0]->value . $descriptor->path;
        }

        $annotations = $this->getAnnotations(Postfix::class, $holder);
        if (isset($annotations[0])) {
            $descriptor->path .= $annotations[0]->value;
        }

        $annotations = $this->getAnnotations(Method::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->methods[] = $annotation->value;
        }

        $annotations = $this->getAnnotations(Consume::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->consumes[] = $annotation->value;
        }

        $annotations = $this->getAnnotations(Produce::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->produces[] = $annotation->value;
        }

        $annotations = $this->getAnnotations(Middleware::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->middlewares[] = $annotation->value;
        }

        $annotations = $this->getAnnotations(Summary::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->summary .= $annotation->value;
        }

        $annotations = $this->getAnnotations(Description::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->description .= $annotation->value;
        }

        $annotations = $this->getAnnotations(Tag::class, $holder);
        foreach ($annotations as $annotation) {
            $descriptor->tags[] = $annotation->value;
        }
    }

    /**
     * Gets the named annotations from the given class or method
     *
     * @param class-string<T> $name
     * @param ReflectionClass|ReflectionMethod $source
     *
     * @return list<T>
     *
     * @template T of object
     */
    private function getAnnotations(string $name, ReflectionClass|ReflectionMethod $source): array
    {
        $result = [];
        $attributes = $source->getAttributes($name);
        foreach ($attributes as $attribute) {
            $result[] = $attribute->newInstance();
        }

        return $result;
    }

    /**
     * Scans the given directory and returns the found classes
     *
     * @param string $dirname
     *
     * @return iterable<ReflectionClass>
     */
    private function getDirectoryClasses(string $dirname): iterable
    {
        /** @var array<non-empty-string, non-empty-string> $filenames */
        $filenames = iterator_to_array(
            new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $dirname,
                        FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_PATHNAME,
                    )
                ),
                '/\.php$/',
            )
        );

        foreach ($filenames as $filename) {
            (static function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require_once $filename;
            })($filename);
        }

        foreach (get_declared_classes() as $fqn) {
            $class = new ReflectionClass($fqn);
            $filename = $class->getFileName();
            if (isset($filenames[$filename])) {
                yield $class;
            }
        }
    }
}
