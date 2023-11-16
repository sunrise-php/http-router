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
use Sunrise\Http\Router\Event\ErrorOccurredEventAbstract;
use Sunrise\Http\Router\Exception\Http\HttpInternalServerErrorException;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Throwable;

use function json_encode;

use const JSON_PARTIAL_OUTPUT_ON_ERROR;

/**
 * Error handling middleware
 *
 * @since 3.0.0
 */
final class ErrorHandlingMiddleware implements MiddlewareInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
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
            $event = new ErrorOccurredEventAbstract($error, $request, $response);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        if ($response->getBody()->getSize() > 0) {
            return $response;
        }

        $response = $response->withHeader('Content-Type', 'application/json; charset=UTF-8');

        /** @var string $payload */
        $payload = json_encode(
            value: ErrorDto::fromHttpError($error),
            flags: JSON_PARTIAL_OUTPUT_ON_ERROR,
        );

        $response->getBody()->write($payload);

        return $response;
    }

    private function handleFatalError(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger?->error($error->getMessage(), ['error' => $error]);

        $httpError = new HttpInternalServerErrorException($error);

        return $this->handleHttpError($httpError, $request);
    }
}
