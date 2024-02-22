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

use BackedEnum;
use Generator;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\Attribute;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\Route as Descriptor;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;
use Sunrise\Http\Router\Entity\MediaType\ServerMediaType;
use Sunrise\Http\Router\Helper\FilesystemHelper;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Route;

use function class_exists;
use function explode;
use function is_dir;
use function join;
use function sprintf;
use function usort;

final class DescriptorLoader implements LoaderInterface
{
    public const DEFAULT_CACHE_KEY = 'router_descriptors';

    /**
     * @var list<string>
     */
    private array $resources = [];

    public function __construct(
        private readonly ?CacheInterface $cache = null,
        private readonly ?string $cacheKey = null,
    ) {
    }

    /**
     * @throws InvalidArgumentException If one of the resources isn't valid.
     */
    public function attach(string ...$resources): void
    {
        foreach ($resources as $resource) {
            if (is_dir($resource) || class_exists($resource)) {
                $this->resources[] = $resource;
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The method %s only accepts class or directory names; ' .
                'however, the resource %s is not one of them.',
                __METHOD__,
                $resource,
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function load(): Generator
    {
        foreach ($this->getDescriptors() as $descriptor) {
            $route = new Route(
                $descriptor->name,
                $descriptor->path,
                $descriptor->methods,
                $descriptor->holder,
            );

            $route->setMiddlewares(...$descriptor->middlewares);
            $route->setAttributes($descriptor->attributes);
            $route->setConstraints($descriptor->constraints);
            $route->setConsumesMediaTypes(...$descriptor->consumes);
            $route->setProducesMediaTypes(...$descriptor->produces);
            $route->setSummary($descriptor->summary);
            $route->setDescription($descriptor->description);
            $route->setTags(...$descriptor->tags);
            $route->setDeprecation($descriptor->isDeprecated);
            $route->setPattern($descriptor->pattern);

            yield $route;
        }
    }

    /**
     * @return list<Descriptor>
     */
    private function getDescriptors(): array
    {
        $cacheKey = $this->cacheKey ?? self::DEFAULT_CACHE_KEY;

        if (isset($this->cache) && $this->cache->has($cacheKey)) {
            /** @var list<Descriptor> */
            return $this->cache->get($cacheKey);
        }

        $result = [];
        foreach ($this->resources as $resource) {
            foreach ($this->getResourceDescriptors($resource) as $descriptor) {
                $descriptor->path = join($descriptor->prefixes) . $descriptor->path;
                $descriptor->pattern = RouteCompiler::compileRoute($descriptor->path, $descriptor->constraints);
                $result[] = $descriptor;
            }
        }

        usort($result, static fn(Descriptor $a, Descriptor $b): int => $b->priority <=> $a->priority);

        if (isset($this->cache)) {
            $this->cache->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * @return Generator<int, Descriptor>
     */
    private function getResourceDescriptors(string $resource): Generator
    {
        if (class_exists($resource)) {
            return yield from $this->getClassDescriptors(new ReflectionClass($resource));
        }

        if (is_dir($resource)) {
            foreach (FilesystemHelper::getDirClasses($resource) as $class) {
                yield from $this->getClassDescriptors($class);
            }
        }
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

        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            // Static methods aren't supported...
            if ($method->isStatic()) {
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

    private function supplementDescriptorFromParentClasses(Descriptor $descriptor, ReflectionClass $child): void
    {
        $parents = [];
        while ($child = $child->getParentClass()) {
            $parents = [$child, ...$parents];
        }

        foreach ($parents as $parent) {
            $this->supplementDescriptorFromClassOrMethod($descriptor, $parent);
        }
    }

    // phpcs:ignore Generic.Files.LineLength
    private function supplementDescriptorFromClassOrMethod(Descriptor $descriptor, ReflectionClass|ReflectionMethod $holder): void
    {
        /** @var list<ReflectionAttribute<Prefix>> $annotations */
        $annotations = $holder->getAttributes(Prefix::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $descriptor->prefixes[] = $annotation->value;
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
            if ($annotation->value instanceof BackedEnum) {
                $descriptor->methods[] = (string) $annotation->value->value;
                continue;
            }

            $descriptor->methods[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Middleware>> $annotations */
        $annotations = $holder->getAttributes(Middleware::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->middlewares[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Attribute>> $annotations */
        $annotations = $holder->getAttributes(Attribute::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->attributes[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Constraint>> $annotations */
        $annotations = $holder->getAttributes(Constraint::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->constraints[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Consumes>> $annotations */
        $annotations = $holder->getAttributes(Consumes::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            if ($annotation->value instanceof MediaTypeInterface) {
                $descriptor->consumes[] = $annotation->value;
                continue;
            }

            $range = explode('/', $annotation->value, 2);

            $descriptor->consumes[] = new ServerMediaType($range[0], $range[1] ?? '*');
        }

        /** @var list<ReflectionAttribute<Produces>> $annotations */
        $annotations = $holder->getAttributes(Produces::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            if ($annotation->value instanceof MediaTypeInterface) {
                $descriptor->produces[] = $annotation->value;
                continue;
            }

            $range = explode('/', $annotation->value, 2);

            $descriptor->produces[] = new ServerMediaType($range[0], $range[1] ?? '*');
        }

        /** @var list<ReflectionAttribute<Summary>> $annotations */
        $annotations = $holder->getAttributes(Summary::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->summary ??= '';
            $descriptor->summary .= $annotation->value;
        }

        /** @var list<ReflectionAttribute<Description>> $annotations */
        $annotations = $holder->getAttributes(Description::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->description ??= '';
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
}
