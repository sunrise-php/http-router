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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Exception\InvalidResponseException;
use Sunrise\Http\Router\Exception\UnsupportedResponseException;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class ResponseResolverChain implements ResponseResolverChainInterface
{
    private bool $isSorted = false;

    public function __construct(
        /** @var array<array-key, ResponseResolverInterface> */
        private array $resolvers = [],
    ) {
    }

    /**
     * @throws InvalidResponseException
     * @throws UnsupportedResponseException
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        $this->isSorted or $this->sortResolvers();
        foreach ($this->resolvers as $resolver) {
            $resolvedResponse = $resolver->resolveResponse($response, $responder, $request);
            if ($resolvedResponse instanceof ResponseInterface) {
                return $this->completeResponse($resolvedResponse, $responder);
            }
        }

        throw new UnsupportedResponseException(sprintf(
            'The responder %s returned an unsupported response that cannot be resolved.',
            self::stringifyResponder($responder),
        ));
    }

    private function completeResponse(
        ResponseInterface $response,
        ReflectionMethod|ReflectionFunction $responder,
    ): ResponseInterface {
        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $responder->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $status = $annotations[0]->newInstance();
            $response = $response->withStatus($status->code, $status->phrase);
        }

        /** @var list<ReflectionAttribute<ResponseHeader>> $annotations */
        $annotations = $responder->getAttributes(ResponseHeader::class);
        foreach ($annotations as $annotation) {
            $header = $annotation->newInstance();
            $response = $response->withHeader($header->name, $header->value);
        }

        return $response;
    }

    private function sortResolvers(): void
    {
        $this->isSorted = usort($this->resolvers, static fn(
            ResponseResolverInterface $a,
            ResponseResolverInterface $b,
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    public static function stringifyResponder(ReflectionMethod|ReflectionFunction $responder): string
    {
        if ($responder instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $responder->getDeclaringClass()->getName(), $responder->getName());
        }

        return sprintf('%s()', $responder->getName());
    }
}