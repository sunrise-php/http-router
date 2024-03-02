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
use LogicException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use SplStack;
use Sunrise\Http\Router\Annotation\Attribute;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Pattern;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\Route as Descriptor;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Helper\FilesystemHelper;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Route;

use function array_map;
use function class_exists;
use function is_dir;
use function is_file;
use function join;
use function sprintf;
use function strtoupper;
use function usort;

final class DescriptorLoader implements LoaderInterface
{
    public const DEFAULT_CACHE_KEY = 'router_descriptors';

    public function __construct(
        /** @var array<array-key, string> */
        private readonly array $resources,
        private readonly ?CacheInterface $cache = null,
        private readonly string $cacheKey = self::DEFAULT_CACHE_KEY,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If one of the resources isn't supported.
     *
     * @throws CacheException If something went wrong while working with the cache.
     */
    public function load(): Generator
    {
        foreach ($this->getDescriptors() as $descriptor) {
            yield new Route(
                $descriptor->name,
                $descriptor->path,
                $descriptor->holder,
                $descriptor->patterns,
                $descriptor->methods,
                $descriptor->attributes,
                $descriptor->middlewares,
                $descriptor->constraints,
                $descriptor->consumes,
                $descriptor->produces,
                $descriptor->summary,
                $descriptor->description,
                $descriptor->tags,
                $descriptor->isDeprecated,
                $descriptor->pattern,
            );
        }
    }

    /**
     * @return list<Descriptor>
     *
     * @throws LogicException If one of the resources isn't supported.
     *
     * @throws CacheException If something went wrong while working with the cache.
     */
    private function getDescriptors(): array
    {
        /** @var list<Descriptor>|null $descriptors */
        $descriptors = $this->cache?->get($this->cacheKey);
        if (isset($descriptors)) {
            return $descriptors;
        }

        $descriptors = [];
        foreach ($this->resources as $resource) {
            foreach ($this->getResourceDescriptors($resource) as $descriptor) {
                $descriptor->path = join($descriptor->prefixes) . $descriptor->path;
                $descriptor->methods = array_map(strtoupper(...), $descriptor->methods);
                $descriptor->pattern = RouteCompiler::compileRoute($descriptor->path, $descriptor->patterns);
                $descriptors[] = $descriptor;
            }
        }

        usort($descriptors, static fn(Descriptor $a, Descriptor $b): int => $b->priority <=> $a->priority);

        $this->cache?->set($this->cacheKey, $descriptors);

        return $descriptors;
    }

    /**
     * @return Generator<int, Descriptor>
     *
     * @throws LogicException If the resource isn't supported.
     */
    private function getResourceDescriptors(string $resource): Generator
    {
        if (is_dir($resource)) {
            foreach (FilesystemHelper::getDirClasses($resource) as $class) {
                yield from $this->getClassDescriptors($class);
            }

            return;
        }

        if (is_file($resource)) {
            foreach (FilesystemHelper::getFileClasses($resource) as $class) {
                yield from $this->getClassDescriptors($class);
            }

            return;
        }

        if (class_exists($resource)) {
            yield from $this->getClassDescriptors(
                new ReflectionClass($resource)
            );

            return;
        }

        throw new LogicException(sprintf(
            'The loader %s only accepts directory, file or class names; ' .
            'however, the resource %s is not one of them.',
            __CLASS__,
            $resource,
        ));
    }

    /**
     * @return Generator<int, Descriptor>
     */
    private function getClassDescriptors(ReflectionClass $class): Generator
    {
        if (!$class->isInstantiable()) {
            return;
        }

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            /** @var list<ReflectionAttribute<Descriptor>> $annotations */
            $annotations = $class->getAttributes(Descriptor::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0]->newInstance();
                $descriptor->holder = $class->getName();
                $this->supplementDescriptorFromParentClasses($descriptor, $class);
                $this->supplementDescriptorFromClassOrMethod($descriptor, $class);
                yield $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            if (!$method->isPublic() || $method->isStatic()) {
                continue;
            }

            /** @var list<ReflectionAttribute<Descriptor>> $annotations */
            $annotations = $method->getAttributes(Descriptor::class);
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

    private function supplementDescriptorFromParentClasses(Descriptor $descriptor, ReflectionClass $class): void
    {
        /** @var SplStack<ReflectionClass> $parents */
        $parents = new SplStack();
        while ($class = $class->getParentClass()) {
            $parents->push($class);
        }

        foreach ($parents as $parent) {
            $this->supplementDescriptorFromClassOrMethod($descriptor, $parent);
        }
    }

    // phpcs:ignore Generic.Files.LineLength
    private function supplementDescriptorFromClassOrMethod(Descriptor $descriptor, ReflectionClass|ReflectionMethod $classOrMethod): void
    {
        /** @var list<ReflectionAttribute<Prefix>> $annotations */
        $annotations = $classOrMethod->getAttributes(Prefix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->prefixes[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Postfix>> $annotations */
        $annotations = $classOrMethod->getAttributes(Postfix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->path .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Pattern>> $annotations */
        $annotations = $classOrMethod->getAttributes(Pattern::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->patterns[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Method>> $annotations */
        $annotations = $classOrMethod->getAttributes(Method::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->methods[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Attribute>> $annotations */
        $annotations = $classOrMethod->getAttributes(Attribute::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->attributes[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Middleware>> $annotations */
        $annotations = $classOrMethod->getAttributes(Middleware::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->middlewares[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Constraint>> $annotations */
        $annotations = $classOrMethod->getAttributes(Constraint::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->constraints[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Consumes>> $annotations */
        $annotations = $classOrMethod->getAttributes(Consumes::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->consumes[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Produces>> $annotations */
        $annotations = $classOrMethod->getAttributes(Produces::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->produces[] = $annotation->value;
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

        /** @var list<ReflectionAttribute<Tag>> $annotations */
        $annotations = $classOrMethod->getAttributes(Tag::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->tags[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Deprecated>> $annotations */
        $annotations = $classOrMethod->getAttributes(Deprecated::class);
        if (isset($annotations[0])) {
            $descriptor->isDeprecated = true;
        }
    }
}
