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

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\SerializableResponse;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\ServerMediaType;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @since 3.0.0
 */
final class SerializableResponseResolver implements ResponseResolverInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly SerializerInterface $serializer,
        private readonly array $context = [],
    ) {
    }

    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request
    ): ?ResponseInterface {
        /** @var list<ReflectionAttribute<SerializableResponse>> $annotations */
        $annotations = $responder->getAttributes(SerializableResponse::class);
        if ($annotations === []) {
            return null;
        }

        $serializableResponse = $annotations[0]->newInstance();

        $serverProducesMediaTypes = [
            new ServerMediaType('application', 'json'),
            new ServerMediaType('application', 'xml'),
            new ServerMediaType('text', 'xml'),
        ];

        $clientPreferredMediaType = ServerRequest::create($request)
            ->getClientPreferredMediaType(...$serverProducesMediaTypes)
                ?? $serverProducesMediaTypes[0];

        $mediaTypeComparator = new MediaTypeComparator();
        if ($serverProducesMediaTypes[0] === $clientPreferredMediaType ||
            $mediaTypeComparator->equals($serverProducesMediaTypes[0], $clientPreferredMediaType)) {
            $format = JsonEncoder::FORMAT;
        } elseif ($mediaTypeComparator->equals($serverProducesMediaTypes[1], $clientPreferredMediaType) ||
            $mediaTypeComparator->equals($serverProducesMediaTypes[2], $clientPreferredMediaType)) {
            $format = XmlEncoder::FORMAT;
        }

        $context = $serializableResponse->context + $this->context;

        $payload = $this->serializer->serialize($response, $format, $context);

        $result = $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-Type', $clientPreferredMediaType . '; charset=UTF-8');

        $result->getBody()->write($payload);

        return $result;
    }
}
