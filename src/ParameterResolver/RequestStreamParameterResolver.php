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
use Psr\Http\Message\StreamInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolverInterface;

/**
 * @since 3.0.0
 */
final class RequestStreamParameterResolver implements ParameterResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        if ($parameter->getType()?->getName() !== StreamInterface::class) {
            return;
        }

        yield $context->getBody();
    }

    public function getWeight(): int
    {
        return 0;
    }
}
