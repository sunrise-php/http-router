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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\ProvideRoute;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;

use function sprintf;

/**
 * ProvideRouteParameterResolver
 *
 * @since 3.0.0
 */
final class ProvideRouteParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param Router $router
     */
    public function __construct(private Router $router)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<ProvideRoute>> $attributes */
        $attributes = $parameter->getAttributes(ProvideRoute::class);
        if ($attributes === []) {
            return;
        }

        $type = $parameter->getType();

        if (! ($type instanceof ReflectionNamedType) ||
            ! ($type->getName() === RouteInterface::class)) {
            throw new LogicException(sprintf(
                'To use the #[ProvideRoute] attribute, the parameter {%s} must be typed with %s.',
                ParameterResolutioner::stringifyParameter($parameter),
                RouteInterface::class,
            ));
        }

        $attribute = $attributes[0]->newInstance();

        yield $this->router->getRoutes()->get($attribute->name);
    }
}
