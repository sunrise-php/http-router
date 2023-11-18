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

namespace Sunrise\Http\Router\ResponseResolving\ResponseResolver;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\SerializableResponse;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * SerializableResponseResolver
 *
 * @link https://github.com/symfony/serializer
 *
 * @since 3.0.0
 */
final class SerializableResponseResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SerializerInterface $serializer
     * @param array<string, mixed> $context
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private SerializerInterface $serializer,
        private array $context = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(
        ServerRequestInterface $request,
        mixed $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ?ResponseInterface {
        /** @var list<ReflectionAttribute<SerializableResponse>> $attributes */
        $attributes = $responder->getAttributes(SerializableResponse::class);
        if ($attributes === []) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        $serverProducesMediaTypes = [MediaType::json(), MediaType::xml()];
        $clientPreferredMediaType = ServerRequest::from($request)
            ->getClientPreferredMediaType(...$serverProducesMediaTypes)
                ?? $serverProducesMediaTypes[0];

        $format = match ($clientPreferredMediaType) {
            $serverProducesMediaTypes[0] => JsonEncoder::FORMAT,
            default => XmlEncoder::FORMAT,
        };

        $result = $this->responseFactory->createResponse(200);
        $mimeType = $clientPreferredMediaType . '; charset=UTF-8';
        $result = $result->withHeader('Content-Type', $mimeType);

        $context = $attribute->context + $this->context;
        $payload = $this->serializer->serialize($response, $format, $context);
        $result->getBody()->write($payload);

        return $result;
    }
}
