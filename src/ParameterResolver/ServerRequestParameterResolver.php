<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ServerRequest;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * ServerRequestParameterResolver
 *
 * @since 3.0.0
 */
final class ServerRequestParameterResolver implements ParameterResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if (!($parameter->getType()->getName() === ServerRequest::class)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ServerRequestInterface */
        $context = $context;

        return ServerRequest::from($context);
    }
}
