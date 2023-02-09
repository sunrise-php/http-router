<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Consume;
use Sunrise\Http\Router\Annotation\Host;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Produce;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolutionerInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * Import functions
 */
use function class_exists;
use function hash;
use function is_dir;
use function is_string;
use function usort;
use function Sunrise\Http\Router\get_dir_classes;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorLoader
 */
final class DescriptorLoader implements LoaderInterface
{

    /**
     * @var list<class-string>
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
     * @var AnnotationReaderInterface|null
     */
    private ?AnnotationReaderInterface $annotationReader = null;

    /**
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache = null;

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
     * @param ParameterResolutionerInterface|null $parameterResolutioner
     * @param ResponseResolutionerInterface|null $responseResolutioner
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null,
        ?ParameterResolutionerInterface $parameterResolutioner = null,
        ?ResponseResolutionerInterface $responseResolutioner = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;

        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver(
            $this->parameterResolutioner ??= new ParameterResolutioner(),
            $this->responseResolutioner ??= new ResponseResolutioner()
        );
    }

    /**
     * Adds the given parameter resolver(s) to the parameter resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver was setted and a parameter resolutioner wasn't passed.
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The descriptor route loader cannot accept the parameter resolver(s) ' .
                'because a custom reference resolver was setted and a parameter resolutioner was not passed'
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
     *         If a custom reference resolver was setted and a response resolutioner wasn't passed.
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        if (!isset($this->responseResolutioner)) {
            throw new LogicException(
                'The descriptor route loader cannot accept the response resolver(s) ' .
                'because a custom reference resolver was setted and a response resolutioner was not passed'
            );
        }

        $this->responseResolutioner->addResolver(...$resolvers);
    }

    /**
     * Uses the default annotation reader
     *
     * @return void
     *
     * @throws LogicException
     *         If the "doctrine/annotations" package isn't installed.
     *
     * @since 3.0.0
     */
    public function useDefaultAnnotationReader(): void
    {
        if (!class_exists(AnnotationReader::class)) {
            throw new LogicException(
                'The annotations reading logic requires an uninstalled "doctrine/annotations" package, ' .
                'run the following command "composer install doctrine/annotations" and try again'
            );
        }

        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Sets the given annotation reader to the loader
     *
     * @param AnnotationReaderInterface|null $annotationReader
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function setAnnotationReader(?AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
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
     * Gets the loader cache key
     *
     * @return string
     *
     * @since 2.10.0
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey ??= hash('md5', 'router:descriptors');
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
     * {@inheritdoc}
     */
    public function attach($resource): void
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'The descriptor route loader only handles string resources'
            );
        }

        if (is_dir($resource)) {
            $classnames = get_dir_classes($resource);
            foreach ($classnames as $classname) {
                $this->resources[] = $classname;
            }

            return;
        }

        if (class_exists($resource)) {
            $this->resources[] = $resource;
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'The descriptor route loader only handles class names or directory paths, ' .
            'however the given resource "%s" is not one of them',
            $resource
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function attachArray(array $resources): void
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * {@inheritdoc}
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
                $descriptor->attributes
            );

            $route->setHost($descriptor->host);
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
     * then tries to cache and return them
     *
     * @return list<Route>
     */
    private function getDescriptors(): array
    {
        $key = $this->getCacheKey();

        if (isset($this->cache) && $this->cache->has($key)) {
            /** @var list<Route> */
            return $this->cache->get($key);
        }

        $result = [];

        foreach ($this->resources as $resource) {
            $descriptors = $this->getClassDescriptors(
                new ReflectionClass($resource)
            );

            foreach ($descriptors as $descriptor) {
                $result[] = $descriptor;
            }
        }

        usort($result, static function (Route $a, Route $b): int {
            return $b->priority <=> $a->priority;
        });

        if (isset($this->cache)) {
            $this->cache->set($key, $result);
        }

        return $result;
    }

    /**
     * Gets descriptors from the given class
     *
     * @param ReflectionClass $class
     *
     * @return list<Route>
     */
    private function getClassDescriptors(ReflectionClass $class): array
    {
        // e.g., interfaces, traits, enums, abstract classes,
        // classes with private constructor...
        if (!$class->isInstantiable()) {
            return [];
        }

        $result = [];

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            $annotations = $this->getClassOrMethodAnnotations($class, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = $class->getName();
                $this->supplementDescriptor($descriptor, $class);
                $result[] = $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            // ignore non-public methods...
            if (!$method->isPublic()) {
                continue;
            }

            $annotations = $this->getClassOrMethodAnnotations($method, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = [$class->getName(), $method->getName()];
                $this->supplementDescriptor($descriptor, $class);
                $this->supplementDescriptor($descriptor, $method);
                $result[] = $descriptor;
            }
        }

        return $result;
    }

    /**
     * Gets annotations from the given class or method
     *
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @template T
     */
    private function getClassOrMethodAnnotations(Reflector $classOrMethod, string $annotationName): array
    {
        $result = [];

        if (PHP_MAJOR_VERSION === 8) {
            /** @var ReflectionAttribute[] */
            $attributes = $classOrMethod->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                /** @var T */
                $result[] = $attribute->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = ($classOrMethod instanceof ReflectionClass) ?
                $this->annotationReader->getClassAnnotations($classOrMethod) :
                $this->annotationReader->getMethodAnnotations($classOrMethod);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    $result[] = $annotation;
                }
            }
        }

        return $result;
    }

    /**
     * Supplements the given descriptor from the given class or method
     *
     * @param Route $descriptor
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     *
     * @return void
     */
    private function supplementDescriptor(Route $descriptor, Reflector $classOrMethod): void
    {
        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Host::class);
        if (isset($annotations[0])) {
            $descriptor->host = $annotations[0]->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Prefix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $annotations[0]->value . $descriptor->path;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Postfix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $descriptor->path . $annotations[0]->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Method::class);
        foreach ($annotations as $annotation) {
            $descriptor->methods[] = $annotation->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Consume::class);
        foreach ($annotations as $annotation) {
            $descriptor->consumes[] = $annotation->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Produce::class);
        foreach ($annotations as $annotation) {
            $descriptor->produces[] = $annotation->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Middleware::class);
        foreach ($annotations as $annotation) {
            $descriptor->middlewares[] = $annotation->value;
        }

        $annotations = $this->getClassOrMethodAnnotations($classOrMethod, Tag::class);
        foreach ($annotations as $annotation) {
            $descriptor->tags[] = $annotation->value;
        }
    }
}
