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
use ReflectionClass;
use Sunrise\Http\Router\Annotation\ResponseBody;
use Sunrise\Http\Router\Entity\MediaType;
use Symfony\Component\Serializer\SerializerInterface;

use function is_object;

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
     * @param SerializerInterface $serializer
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        private SerializerInterface $serializer,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(mixed $value, mixed $context): ?ResponseInterface
    {
        if (!is_object($value)) {
            return null;
        }

        $class = new ReflectionClass($value::class);
        $attributes = $class->getAttributes(ResponseBody::class);
        if ($attributes === []) {
            return null;
        }

        /**
         * @var ResponseBody $responseBody
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $responseBody = $attributes[0]->newInstance();

        $response = $this->responseFactory->createResponse($responseBody->statusCode);

        $mediaType = match ($responseBody->format) {
            ResponseBody::FORMAT_CSV => MediaType::TEXT_CSV,
            ResponseBody::FORMAT_JSON => MediaType::APPLICATION_JSON,
            ResponseBody::FORMAT_XML => MediaType::APPLICATION_XML,
            ResponseBody::FORMAT_YAML => MediaType::APPLICATION_YAML,
            default => null,
        };

        if (isset($mediaType)) {
            $response = $response->withHeader('Content-Type', $mediaType);
        }

        foreach ($responseBody->headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response->getBody()->write($this->serializer->serialize($value, $responseBody->format));

        return $response;
    }
}
