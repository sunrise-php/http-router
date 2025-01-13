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

use Generator;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\DefaultAttribute;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\Path;
use Sunrise\Http\Router\Annotation\PathPostfix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Annotation\Pattern;
use Sunrise\Http\Router\Annotation\Priority;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\Route as Descriptor;
use Sunrise\Http\Router\Annotation\SerializableResponse;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Helper\ClassFinder;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Route;

use function array_map;
use function class_exists;
use function implode;
use function is_dir;
use function is_file;
use function sprintf;
use function strtoupper;
use function usort;

/**
 * @since 2.10.0
 */
final class DescriptorLoader implements DescriptorLoaderInterface
{
    /**
     * @since 3.0.0
     */
    public const DESCRIPTORS_CACHE_KEY = 'sunrise_http_router_descriptors';

    public function __construct(
        /** @var array<array-key, string> */
        private readonly array $resources,
        private readonly ?CacheInterface $cache = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function load(): Generator
    {
        foreach ($this->getDescriptors() as $descriptor) {
            yield new Route(
                name: $descriptor->name,
                path: $descriptor->path,
                requestHandler: $descriptor->holder,
                patterns: $descriptor->patterns,
                methods: $descriptor->methods,
                attributes: $descriptor->attributes,
                middlewares: $descriptor->middlewares,
                consumes: $descriptor->consumes,
                produces: $descriptor->produces,
                tags: $descriptor->tags,
                summary: $descriptor->summary,
                description: $descriptor->description,
                isDeprecated: $descriptor->isDeprecated,
                isApiOperation: $descriptor->isApiOperation,
                apiOperationFields: $descriptor->apiOperationFields,
                pattern: $descriptor->pattern,
            );
        }
    }

    /**
     * @throws CacheException
     */
    public function clearCache(): void
    {
        $this->cache?->delete(self::DESCRIPTORS_CACHE_KEY);
    }

    /**
     * @return list<Descriptor>
     *
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function getDescriptors(): array
    {
        /** @var list<Descriptor>|null $descriptors */
        $descriptors = $this->cache?->get(self::DESCRIPTORS_CACHE_KEY);
        if ($descriptors !== null) {
            return $descriptors;
        }

        $descriptors = [];
        foreach ($this->resources as $resource) {
            foreach (self::getResourceDescriptors($resource) as $descriptor) {
                $descriptors[] = $descriptor;
            }
        }

        usort($descriptors, static fn(Descriptor $a, Descriptor $b): int => $b->priority <=> $a->priority);

        $this->cache?->set(self::DESCRIPTORS_CACHE_KEY, $descriptors);

        return $descriptors;
    }

    /**
     * @return Generator<int, Descriptor>
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private static function getResourceDescriptors(string $resource): Generator
    {
        if (is_dir($resource)) {
            foreach (ClassFinder::getDirClasses($resource) as $class) {
                yield from self::getClassDescriptors($class);
            }

            return;
        }

        if (is_file($resource)) {
            foreach (ClassFinder::getFileClasses($resource) as $class) {
                yield from self::getClassDescriptors($class);
            }

            return;
        }

        if (class_exists($resource)) {
            yield from self::getClassDescriptors(new ReflectionClass($resource));

            return;
        }

        throw new InvalidArgumentException(sprintf(
            'The loader %s only accepts directory, file or class names; ' .
            'however, the resource %s is not one of them.',
            self::class,
            $resource,
        ));
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return Generator<int, Descriptor>
     *
     * @throws InvalidArgumentException
     */
    private static function getClassDescriptors(ReflectionClass $class): Generator
    {
        if (!$class->isInstantiable()) {
            return;
        }

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            /** @var list<ReflectionAttribute<Descriptor>> $annotations */
            $annotations = $class->getAttributes(Descriptor::class, ReflectionAttribute::IS_INSTANCEOF);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0]->newInstance();
                $descriptor->holder = $class->getName();
                self::enrichDescriptorFromParentClasses($descriptor, $class);
                self::enrichDescriptorFromClassOrMethod($descriptor, $class);
                self::completeDescriptor($descriptor);
                yield $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            if (!$method->isPublic() || $method->isStatic()) {
                continue;
            }

            /** @var list<ReflectionAttribute<Descriptor>> $annotations */
            $annotations = $method->getAttributes(Descriptor::class, ReflectionAttribute::IS_INSTANCEOF);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0]->newInstance();
                $descriptor->holder = [$class->getName(), $method->getName()];
                self::enrichDescriptorFromParentClasses($descriptor, $class);
                self::enrichDescriptorFromClassOrMethod($descriptor, $class);
                self::enrichDescriptorFromClassOrMethod($descriptor, $method);
                self::completeDescriptor($descriptor);
                yield $descriptor;
            }
        }
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private static function enrichDescriptorFromParentClasses(Descriptor $descriptor, ReflectionClass $class): void
    {
        foreach (ClassFinder::getParentClasses($class) as $parent) {
            self::enrichDescriptorFromClassOrMethod($descriptor, $parent);
        }
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $classOrMethod
     *
     * @throws InvalidArgumentException
     */
    private static function enrichDescriptorFromClassOrMethod(
        Descriptor $descriptor,
        ReflectionClass|ReflectionMethod $classOrMethod,
    ): void {
        /** @var list<ReflectionAttribute<NamePrefix>> $annotations */
        $annotations = $classOrMethod->getAttributes(NamePrefix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->namePrefixes[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Path>> $annotations */
        $annotations = $classOrMethod->getAttributes(Path::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->path = $annotation->value;
        }

        /** @var list<ReflectionAttribute<PathPrefix>> $annotations */
        $annotations = $classOrMethod->getAttributes(PathPrefix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->pathPrefixes[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<PathPostfix>> $annotations */
        $annotations = $classOrMethod->getAttributes(PathPostfix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->path .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Pattern>> $annotations */
        $annotations = $classOrMethod->getAttributes(Pattern::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->patterns[$annotation->variableName] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Method>> $annotations */
        $annotations = $classOrMethod->getAttributes(Method::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            foreach ($annotation->values as $value) {
                $descriptor->methods[] = $value;
            }
        }

        /** @var list<ReflectionAttribute<DefaultAttribute>> $annotations */
        $annotations = $classOrMethod->getAttributes(DefaultAttribute::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->attributes[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Middleware>> $annotations */
        $annotations = $classOrMethod->getAttributes(Middleware::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            foreach ($annotation->values as $value) {
                $descriptor->middlewares[] = $value;
            }
        }

        /** @var list<ReflectionAttribute<Consumes>> $annotations */
        $annotations = $classOrMethod->getAttributes(Consumes::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            foreach ($annotation->values as $value) {
                $descriptor->consumes[] = $value;
            }
        }

        /** @var list<ReflectionAttribute<Produces>> $annotations */
        $annotations = $classOrMethod->getAttributes(Produces::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            foreach ($annotation->values as $value) {
                $descriptor->produces[] = $value;
            }
        }

        /** @var list<ReflectionAttribute<SerializableResponse>> $annotations */
        $annotations = $classOrMethod->getAttributes(SerializableResponse::class, ReflectionAttribute::IS_INSTANCEOF);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            foreach ($annotation->getMediaTypes() as $mediaType) {
                $descriptor->produces[] = $mediaType;
            }
        }

        /** @var list<ReflectionAttribute<Tag>> $annotations */
        $annotations = $classOrMethod->getAttributes(Tag::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            foreach ($annotation->values as $value) {
                $descriptor->tags[] = $value;
            }
        }

        /** @var list<ReflectionAttribute<Summary>> $annotations */
        $annotations = $classOrMethod->getAttributes(Summary::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->summary .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Description>> $annotations */
        $annotations = $classOrMethod->getAttributes(Description::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->description .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Deprecated>> $annotations */
        $annotations = $classOrMethod->getAttributes(Deprecated::class);
        if (isset($annotations[0])) {
            $descriptor->isDeprecated = true;
        }

        /** @var list<ReflectionAttribute<Priority>> $annotations */
        $annotations = $classOrMethod->getAttributes(Priority::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->priority = $annotation->value;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function completeDescriptor(Descriptor $descriptor): void
    {
        $descriptor->name = implode($descriptor->namePrefixes) . $descriptor->name;
        $descriptor->path = implode($descriptor->pathPrefixes) . $descriptor->path;

        $descriptor->methods = array_map(strtoupper(...), $descriptor->methods);

        $descriptor->pattern = RouteCompiler::compileRoute($descriptor->path, $descriptor->patterns);
    }
}
