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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\MediaTypeInterface;
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\LanguageInterface;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Sunrise\Http\Router\View\ErrorView;
use Sunrise\Http\Router\View\ViolationView;
use Sunrise\Translator\TranslatorManagerInterface;
use Throwable;

use function sprintf;

final class ErrorHandlingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly CodecManagerInterface $codecManager,
        /** @var array<array-key, mixed> */
        private readonly array $codecContext,
        /** @var list<MediaTypeInterface> */
        private readonly array $producedMediaTypes,
        private readonly MediaTypeInterface $defaultMediaType,
        private readonly TranslatorManagerInterface $translatorManager,
        /** @var list<LanguageInterface> */
        private readonly array $producedLanguages,
        private readonly LanguageInterface $defaultLanguage,
        private readonly LoggerInterface $logger,
        private readonly ?int $fatalErrorStatusCode = null,
        private readonly ?string $fatalErrorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpException $e) {
            return $this->handleHttpError($e, $request);
        } catch (Throwable $e) {
            return $this->handleFatalError($e, $request);
        }
    }

    private function handleHttpError(HttpException $error, ServerRequestInterface $request): ResponseInterface
    {
        $clientPreferredMediaType = ServerRequest::create($request)
            ->getClientPreferredMediaType(...$this->producedMediaTypes)
                ?? $this->defaultMediaType;

        $clientPreferredLanguage = ServerRequest::create($request)
            ->getClientPreferredLanguage(...$this->producedLanguages)
                ?? $this->defaultLanguage;

        return $this->createErrorResponse($error, $clientPreferredMediaType, $clientPreferredLanguage);
    }

    private function handleFatalError(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->error($error->getMessage(), [
            'error' => $error,
            'request' => $request,
        ]);

        $httpError = HttpExceptionFactory::internalServerError(
            message: $this->fatalErrorMessage,
            code: $this->fatalErrorStatusCode,
            previous: $error,
        );

        return $this->handleHttpError($httpError, $request);
    }

    private function createErrorResponse(
        HttpException $error,
        MediaTypeInterface $mediaType,
        LanguageInterface $language,
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse($error->getCode());
        foreach ($error->getHeaderFields() as [$fieldName, $fieldValue]) {
            $response = $response->withHeader($fieldName, $fieldValue);
        }

        $errorView = $this->createErrorView($error, $language);
        $responseContent = $this->codecManager->encode($mediaType, $errorView, $this->codecContext);
        $responseContentType = sprintf('%s; charset=UTF-8', $mediaType->getIdentifier());

        return $response->withHeader(HeaderName::CONTENT_TYPE, $responseContentType)
            ->withBody($this->streamFactory->createStream($responseContent));
    }

    private function createErrorView(
        HttpException $error,
        LanguageInterface $language,
    ): ErrorView {
        $message = $this->translatorManager->translate(
            domain: $error->getTranslationDomain(),
            locale: $language->getCode(),
            template: $error->getMessageTemplate(),
            placeholders: $error->getMessagePlaceholders(),
        );

        $violationViews = [];
        foreach ($error->getConstraintViolations() as $violation) {
            $violationViews[] = $this->createViolationView($violation, $language);
        }

        return new ErrorView(
            message: $message,
            violations: $violationViews,
        );
    }

    private function createViolationView(
        ConstraintViolationInterface $violation,
        LanguageInterface $language,
    ): ViolationView {
        $message = $this->translatorManager->translate(
            domain: $violation->getTranslationDomain(),
            locale: $language->getCode(),
            template: $violation->getMessageTemplate(),
            placeholders: $violation->getMessagePlaceholders(),
        );

        return new ViolationView(
            source: $violation->getPropertyPath(),
            message: $message,
            code: $violation->getCode(),
        );
    }
}
