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

namespace Sunrise\Http\Router\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Http\Router\Dto\ErrorDto;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorOccurredEvent;
use Sunrise\Http\Router\Exception\Http\HttpInternalServerErrorException;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\ServerRequest;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

use const JSON_PARTIAL_OUTPUT_ON_ERROR;

/**
 * Error handling middleware
 *
 * @link https://github.com/symfony/serializer
 *
 * @since 3.0.0
 */
final class ErrorHandlingMiddleware implements MiddlewareInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private SerializerInterface $serializer,
        private ?EventDispatcherInterface $eventDispatcher = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpExceptionInterface $error) {
            return $this->handleHttpError($error, $request);
        } catch (Throwable $error) {
            return $this->handleFatalError($error, $request);
        }
    }

    private function handleHttpError(HttpExceptionInterface $error, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($error->getStatusCode());
        foreach ($error->getHeaders() as [$fieldName, $fieldValue]) {
            $response = $response->withHeader($fieldName, $fieldValue);
        }

        if (isset($this->eventDispatcher)) {
            $event = new ErrorOccurredEvent($error, $request, $response);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        if ($response->getBody()->getSize() > 0) {
            return $response;
        }

        $serverProducesMediaTypes = [MediaType::json(), MediaType::xml()];

        $clientPreferredMediaType = ServerRequest::from($request)
            ->getClientPreferredMediaType(...$serverProducesMediaTypes);

        $contentType = $clientPreferredMediaType->build(['charset' => 'UTF-8']);
        $response = $response->withHeader('Content-Type', $contentType);

        $response->getBody()->write(
            match ($clientPreferredMediaType) {
                $serverProducesMediaTypes[0] => $this->renderJsonError($error),
                $serverProducesMediaTypes[1] => $this->renderXmlError($error),
            }
        );

        return $response;
    }

    private function handleFatalError(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger?->error($error->getMessage(), ['error' => $error]);

        $httpError = new HttpInternalServerErrorException($error);

        return $this->handleHttpError($httpError, $request);
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
