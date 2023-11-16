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

namespace Sunrise\Http\Router\EventListener;

use Sunrise\Http\Router\Dto\ErrorDto;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorOccurredEventAbstract;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PARTIAL_OUTPUT_ON_ERROR;

/**
 * Renders errors using the symfony/serializer package
 *
 * @link https://github.com/symfony/serializer
 *
 * @since 3.0.0
 */
final class ErrorOccurredEventListenerRenderingSerializedError
{

    /**
     * Constructor of the class
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * Handles the given event
     *
     * @param ErrorOccurredEventAbstract $event
     *
     * @return ErrorOccurredEventAbstract
     */
    public function __invoke(ErrorOccurredEventAbstract $event): ErrorOccurredEventAbstract
    {
        $error = $event->getError();
        if (! $error instanceof HttpExceptionInterface) {
            return $event;
        }

        $serverProducesMediaTypes = [MediaType::json(), MediaType::xml()];
        $clientPreferredMediaType = ServerRequest::from($event->getRequest())
            ->getClientPreferredMediaType(...$serverProducesMediaTypes)
                ?? $serverProducesMediaTypes[0];

        $mimeType = $clientPreferredMediaType . '; charset=UTF-8';
        $response = $event->getResponse()->withHeader('Content-Type', $mimeType);

        $response->getBody()->write(
            match ($clientPreferredMediaType) {
                $serverProducesMediaTypes[0] => $this->renderJsonError($error),
                default => $this->renderXmlError($error),
            }
        );

        $event->setResponse($response);

        return $event;
    }

    private function renderJsonError(HttpExceptionInterface $error): string
    {
        $view = ErrorDto::fromHttpError($error);

        return $this->serializer->serialize($view, JsonEncoder::FORMAT, [
            JsonEncode::OPTIONS => JSON_PARTIAL_OUTPUT_ON_ERROR,
        ]);
    }

    private function renderXmlError(HttpExceptionInterface $error): string
    {
        $view = ErrorDto::fromHttpError($error);

        return $this->serializer->serialize($view, XmlEncoder::FORMAT, [
            XmlEncoder::ROOT_NODE_NAME => 'error',
        ]);
    }
}
