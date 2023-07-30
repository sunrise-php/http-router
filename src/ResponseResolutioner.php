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

namespace Sunrise\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

use function get_debug_type;
use function sprintf;

/**
 * ResponseResolutioner
 *
 * @since 3.0.0
 */
final class ResponseResolutioner implements ResponseResolutionerInterface
{

    /**
     * @var list<ResponseResolverInterface>
     */
    private array $resolvers = [];

    /**
     * @inheritDoc
     */
    public function addResolver(ResponseResolverInterface ...$resolvers): void
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the value cannot be resolved to PSR-7 response.
     */
    public function resolveResponse(mixed $value, mixed $context): ResponseInterface
    {
        if ($value instanceof ResponseInterface) {
            return $value;
        }

        foreach ($this->resolvers as $resolver) {
            $response = $resolver->resolveResponse($value, $context);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }

        throw new LogicException(sprintf(
            'Unable to resolve the value {%s} to PSR-7 response.',
            get_debug_type($value),
        ));
    }
}
