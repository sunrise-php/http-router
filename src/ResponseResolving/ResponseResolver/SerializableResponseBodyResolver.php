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
use Sunrise\Http\Router\Annotation\SerializableResponseBody;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * SerializableResponseBodyResolver
 *
 * @link https://github.com/symfony/serializer
 *
 * @since 3.0.0
 */
final class SerializableResponseBodyResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SerializerInterface $serializer
     * @param array<non-empty-string, mixed> $context
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
        /** @var list<ReflectionAttribute<SerializableResponseBody>> $attributes */
        $attributes = $responder->getAttributes(SerializableResponseBody::class);
        if ($attributes === []) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        $serverProducesMediaTypes = [MediaType::json(), MediaType::xml()];

        $clientPreferredMediaType = ServerRequest::from($request)
            ->getClientPreferredMediaType(...$serverProducesMediaTypes);

        $format = match ($clientPreferredMediaType) {
            $serverProducesMediaTypes[0] => JsonEncoder::FORMAT,
            $serverProducesMediaTypes[1] => XmlEncoder::FORMAT,
        };

        $context = $attribute->context + $this->context;

        $contentType = $clientPreferredMediaType->build(['charset' => 'UTF-8']);
        $result = $this->responseFactory->createResponse(200)->withHeader('Content-Type', $contentType);
        $result->getBody()->write($this->serializer->serialize($response, $format, $context));

        return $result;
    }
}
