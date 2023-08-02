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

namespace Sunrise\Http\Router\ResponseResolver;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseBody;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolutioner;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\SerializerInterface;

use function next;
use function reset;
use function sprintf;

/**
 * ResponseBodyResponseResolver
 *
 * @since 3.0.0
 */
final class ResponseBodyResponseResolver implements ResponseResolverInterface
{

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    public const MEDIA_TYPE_FORMATS = [
        MediaType::APPLICATION_JSON => 'json',
        MediaType::APPLICATION_XML => 'xml',
        MediaType::APPLICATION_YAML => 'yaml',
    ];

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SerializerInterface $serializer
     * @param non-empty-string $defaultMediaType
     * @param array<non-empty-string, non-empty-string> $mediaTypeFormats
     * @param array<non-empty-string, mixed> $serializationContext
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private SerializerInterface $serializer,
        private string $defaultMediaType = MediaType::APPLICATION_JSON,
        private array $mediaTypeFormats = self::MEDIA_TYPE_FORMATS,
        private array $serializationContext = [],
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveResponse(
        mixed $response,
        ServerRequestInterface $request,
        ReflectionFunction|ReflectionMethod $source,
    ) : ?ResponseInterface {
        if ($source->getAttributes(ResponseBody::class) === []) {
            return null;
        }

        $mediaType = $this->defaultMediaType;

        /** @var RouteInterface|null $route */
        $route = $request->getAttribute('@route');
        if ($route instanceof RouteInterface) {
            $producedMediaTypes = $route->getProducedMediaTypes();
            if (!empty($producedMediaTypes)) {
                $mediaType = reset($producedMediaTypes);
                $consumedMediaTypes = ServerRequest::from($request)->getClientConsumedMediaTypes();
                while ($producedMediaType = next($producedMediaTypes)) {
                    if (isset($consumedMediaTypes[$producedMediaType])) {
                        $mediaType = $producedMediaType;
                        break;
                    }
                }
            }
        }

        if (!isset($this->mediaTypeFormats[$mediaType])) {
            throw new LogicException(sprintf(
                'The response {%s} cannot be serialized. ' .
                'Make sure that the resolver %s is configured correctly, ' .
                'and also ensure that the current route provides media types known to the resolver.',
                ResponseResolutioner::stringifyResponse($response, $source),
                ResponseBodyResponseResolver::class,
            ));
        }

        $payload = $this->serializer->serialize(
            $response,
            $this->mediaTypeFormats[$mediaType],
            $this->serializationContext,
        );

        $contentType = sprintf('%s; charset=UTF-8', $mediaType);

        $result = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', $contentType);

        $result->getBody()->write($payload);

        return $result;
    }
}
