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
     * @throws LogicException If the response cannot be resolved to PSR-7 response.
     */
    public function resolveResponse(
        mixed $response,
        ServerRequestInterface $request,
        ReflectionFunction|ReflectionMethod $source,
    ) : ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolveResponse($response, $request, $source);
            if ($result instanceof ResponseInterface) {
                return $this->supplementResponse($result, $source);
            }
        }

        throw new LogicException(sprintf(
            'Unable to resolve the response {%s->%s} to PSR-7 response',
            self::stringifySource($source),
            get_debug_type($response),
        ));
    }

    /**
     * Supplements the given response
     *
     * @param ResponseInterface $response
     * @param ReflectionFunction|ReflectionMethod $source
     *
     * @return ResponseInterface
     */
    private function supplementResponse(
        ResponseInterface $response,
        ReflectionFunction|ReflectionMethod $source,
    ) : ResponseInterface {
        /** @var list<ReflectionAttribute<ResponseStatus>> $attributes */
        $attributes = $source->getAttributes(ResponseStatus::class);
        if (isset($attributes[0])) {
            $status = $attributes[0]->newInstance();
            $response = $response->withStatus($status->code);
        }

        /** @var list<ReflectionAttribute<ResponseHeader>> $attributes */
        $attributes = $source->getAttributes(ResponseHeader::class);
        foreach ($attributes as $attribute) {
            $header = $attribute->newInstance();
            $response = $response->withHeader($header->name, $header->value);
        }

        return $response;
    }

    /**
     * Stringifies the given source of a response
     *
     * @param ReflectionFunction|ReflectionMethod $source
     *
     * @return non-empty-string
     */
    public static function stringifySource(ReflectionFunction|ReflectionMethod $source): string
    {
        if ($source instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $source->getDeclaringClass()->getName(), $source->getName());
        }

        return sprintf('%s()', $source->getName());
    }
}
