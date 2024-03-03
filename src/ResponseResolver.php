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

use function sprintf;

/**
 * @since 3.0.0
 */
final class ResponseResolver
{
    public function __construct(
        /** @var array<array-key, ResponseResolverInterface> */
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
    ) : ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $this->handleResponse($response, $responder);
        }

        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolveResponse($response, $responder, $request);
            if ($result instanceof ResponseInterface) {
                return $this->handleResponse($result, $responder);
            }
        }

        throw new LogicException(sprintf(
            'The responder %s returned an unsupported response.',
            self::stringifyResponder($responder),
        ));
    }

    private function handleResponse(
        ResponseInterface $response,
        ReflectionMethod|ReflectionFunction $responder,
    ) : ResponseInterface {
        /** @var ReflectionAttribute $attributes */
        $attributes = $responder->getAttributes(ResponseStatus::class);
        if (isset($attributes[0])) {
            $status = $attributes[0]->newInstance();
            $response = $response->withStatus($status->code, $status->phrase);
        }

        /** @var ReflectionAttribute $attributes */
        $attributes = $responder->getAttributes(ResponseHeader::class);
        foreach ($attributes as $attribute) {
            $header = $attribute->newInstance();
            $response = $response->withHeader($header->name, $header->value);
        }

        return $response;
    }

    public static function stringifyResponder(ReflectionMethod|ReflectionFunction $function): string
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $function->getDeclaringClass()->getName(), $function->getName());
        }

        return sprintf('%s()', $function->getName());
    }
}
