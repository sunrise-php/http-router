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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Router\Exception\ResolvingResponseException;
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
     * @var RequestInterface|null
     */
    private ?RequestInterface $request = null;

    /**
     * @var list<ResponseResolverInterface>
     */
    private array $resolvers = [];

    /**
     * @inheritDoc
     */
    public function withRequest(RequestInterface $context): static
    {
        $clone = clone $this;
        $clone->request = $context;

        return $clone;
    }

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
     */
    public function resolveResponse($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsResponse($response, $this->request)) {
                return $resolver->resolveResponse($response, $this->request);
            }
        }

        throw new ResolvingResponseException(sprintf(
            'Unable to resolve the response {%s}',
            get_debug_type($response),
        ));
    }
}
