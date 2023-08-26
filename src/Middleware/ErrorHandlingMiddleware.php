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
use SimpleXMLElement;
use Sunrise\Http\Router\Dto\ErrorDto;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorEvent;
use Sunrise\Http\Router\Exception\Http\HttpInternalServerErrorException;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\ServerRequest;
use Throwable;

use function extension_loaded;
use function json_encode;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const LIBXML_COMPACT;
use const LIBXML_NOERROR;
use const LIBXML_NOWARNING;

/**
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
        $serverProducesMediaTypes = [MediaType::html(), MediaType::json()];

        if (extension_loaded('simplexml')) {
            $serverProducesMediaTypes[] = MediaType::xml();
        }

        $clientPreferredMediaType = ServerRequest::from($request)
            ->getClientPreferredMediaType(...$serverProducesMediaTypes);

        $response = $this->responseFactory->createResponse($error->getStatusCode())
            ->withHeader('Content-Type', $clientPreferredMediaType->__toString());

        foreach ($error->getHeaders() as [$fieldName, $fieldValue]) {
            $response = $response->withHeader($fieldName, $fieldValue);
        }

        if ($clientPreferredMediaType->equals(MediaType::html())) {
            $response->getBody()->write($this->renderHtmlError($error));
        } elseif ($clientPreferredMediaType->equals(MediaType::json())) {
            $response->getBody()->write($this->renderJsonError($error));
        } elseif ($clientPreferredMediaType->equals(MediaType::xml())) {
            $response->getBody()->write($this->renderXmlError($error));
        }

        if (isset($this->eventDispatcher)) {
            $event = new ErrorEvent($error, $request, $response);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        return $response;
    }

    private function handleFatalError(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger?->error($error->getMessage(), ['error' => $error]);

        $httpError = new HttpInternalServerErrorException(previous: $error);

        return $this->handleHttpError($httpError, $request);
    }

    private function renderHtmlError(HttpExceptionInterface $error): string
    {
        $view = __DIR__ . '/../../resources/views/error.phtml';

        return $this->loadView($view, $error);
    }

    private function renderJsonError(HttpExceptionInterface $error): string
    {
        $view = new ErrorDto(
            $error->getMessage(),
            $error->getViolations(),
        );

        return json_encode($view, flags: JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    private function renderXmlError(HttpExceptionInterface $error): string
    {
        $xmlBlank = '<?xml version="1.0" encoding="UTF-8"?><error/>';
        $xmlOptions = LIBXML_COMPACT | LIBXML_NOERROR | LIBXML_NOWARNING;

        $errorChild = new SimpleXMLElement($xmlBlank, $xmlOptions);
        $errorChild->addChild('message', $error->getMessage());

        foreach ($error->getViolations() as $violation) {
            /** @var SimpleXMLElement $violationsChild */
            $violationsChild = $errorChild->addChild('violations');
            $violationsChild->addChild('message', $violation->message);
            $violationsChild->addChild('source', $violation->source);
            $violationsChild->addChild('code', $violation->code);
        }

        /** @var string */
        return $errorChild->asXML();
    }

    private function loadView(string $filename, HttpExceptionInterface $error): string
    {
        try {
            ob_start();

            (function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                include $filename;
            })->call($error, $filename);

            return ob_get_clean();
        } catch (Throwable $exception) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            throw $exception;
        }
    }
}
