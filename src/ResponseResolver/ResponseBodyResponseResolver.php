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

use function in_array;
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
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SerializerInterface $serializer
     * @param non-empty-string $defaultFormat
     * @param array<non-empty-string, non-empty-string> $formats
     * @param array<non-empty-string, mixed> $serializationContext
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private SerializerInterface      $serializer,
        private string                   $defaultFormat = 'json',
        private array                    $formats = self::MEDIA_FORMATS,
        private array                    $serializationContext = [],
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

        $format = $this->defaultFormat;

        /** @var RouteInterface|null $route */
        $route = $request->getAttribute('@route');
        if ($route instanceof RouteInterface) {
            $mediaRange = ServerRequest::from($request)
                ->getClientPreferredMediaType(...$route->getProducesMediaTypes())
                ?->getMediaRange();

            if (isset($mediaRange) && isset($this->formats[$mediaRange])) {
                $format = $this->formats[$mediaRange];
            }
        }

        $payload = $this->serializer->serialize($response, $format, $this->serializationContext);

        $contentType = sprintf('%s; charset=UTF-8', $mediaType->getMediaRange());

        $result = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', $contentType);

        $result->getBody()->write($payload);

        return $result;
    }
}
