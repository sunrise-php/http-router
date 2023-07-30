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

namespace Sunrise\Http\Router\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestAttribute;
use Sunrise\Http\Router\Exception\LogicException;

/**
 * RequestAttributeParameterResolver
 *
 * @since 3.0.0
 */
final class RequestAttributeParameterResolver implements ParameterResolverInterface
{

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $attributes = $parameter->getAttributes(RequestAttribute::class);
        if ($attributes === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        /**
         * @var RequestAttribute $attribute
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $attribute = $attributes[0]->newInstance();

        $value = $context->getAttribute($attribute->key);

        yield $value;
    }
}
