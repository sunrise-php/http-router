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

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

use function get_debug_type;
use function sprintf;

/**
 * @since 3.0.0
 */
final class ResponseResolver
{
    public function __construct(
        /** @var ResponseResolverInterface[] */
        private readonly array $resolvers,
    ) {
    }

    /**
     * @throws LogicException If the response couldn't be resolved to PSR-7 response.
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        foreach ($this->resolvers as $resolver) {
            $resolvedResponse = $resolver->resolveResponse($response, $responder, $request);
            if ($resolvedResponse instanceof ResponseInterface) {
                return $this->completeResponse($resolvedResponse, $responder);
            }
        }

        throw new LogicException(sprintf(
            'The responder %s returned the unsupported response "%s".',
            self::stringifyResponder($responder),
            get_debug_type($response),
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

    public static function stringifyResponder(ReflectionMethod|ReflectionFunction $responder): string
    {
        if ($responder instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $responder->getDeclaringClass()->getName(), $responder->getName());
        }

        return sprintf('%s()', $responder->getName());
    }
}
