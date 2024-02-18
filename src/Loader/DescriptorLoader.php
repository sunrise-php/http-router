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
use Psr\SimpleCache\CacheInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\ConsumesEncoding;
use Sunrise\Http\Router\Annotation\ConsumesMediaType;
use Sunrise\Http\Router\Annotation\DefaultAttribute;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\ProducesEncoding;
use Sunrise\Http\Router\Annotation\ProducesMediaType;
use Sunrise\Http\Router\Annotation\Route as Descriptor;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Helper\FilesystemHelper;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Route;

use function class_exists;
use function hash;
use function is_dir;
use function join;
use function sprintf;
use function usort;

final class DescriptorLoader implements LoaderInterface
{
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
            $route->setConsumesEncodings(...$descriptor->consumesEncodings);
            $route->setProducesEncodings(...$descriptor->producesEncodings);
            $route->setConsumesMediaTypes(...$descriptor->consumesMediaTypes);
            $route->setProducesMediaTypes(...$descriptor->producesMediaTypes);
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
        $cacheKey = $this->cacheKey ?? hash('md5', __METHOD__);

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
            foreach (FilesystemHelper::getDirectoryClasses($resource) as $class) {
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
            $descriptor->methods[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Middleware>> $annotations */
        $annotations = $holder->getAttributes(Middleware::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->middlewares[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<DefaultAttribute>> $annotations */
        $annotations = $holder->getAttributes(DefaultAttribute::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->attributes[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<Constraint>> $annotations */
        $annotations = $holder->getAttributes(Constraint::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->constraints[$annotation->name] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<ConsumesEncoding>> $annotations */
        $annotations = $holder->getAttributes(ConsumesEncoding::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->consumesEncodings[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<ProducesEncoding>> $annotations */
        $annotations = $holder->getAttributes(ProducesEncoding::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->producesEncodings[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<ConsumesMediaType>> $annotations */
        $annotations = $holder->getAttributes(ConsumesMediaType::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->consumesMediaTypes[] = $annotation->value;
        }

        /** @var list<ReflectionAttribute<ProducesMediaType>> $annotations */
        $annotations = $holder->getAttributes(ProducesMediaType::class);
        foreach ($annotations as $annotation) {
            $annotation = $annotation->newInstance();
            $descriptor->producesMediaTypes[] = $annotation->value;
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
