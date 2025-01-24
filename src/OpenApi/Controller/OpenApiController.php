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

namespace Sunrise\Http\Router\OpenApi\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\GetRoute;
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;

/**
 * @since 3.0.0
 */
#[GetRoute(self::ROUTE_NAME, self::ROUTE_PATH)]
final class OpenApiController implements RequestHandlerInterface
{
    public const ROUTE_NAME = '@openapi';
    public const ROUTE_PATH = '/openapi';

    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly OpenApiDocumentManagerInterface $openApiDocumentManager,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $responseBody = $this->streamFactory->createStreamFromResource(
            $this->openApiDocumentManager->openDocument(),
        );

        $responseContentType = $this->openApiConfiguration->documentMediaType->getIdentifier();
        $responseContentType .= '; charset=UTF-8'; // It should be a text data type...

        return $this->responseFactory->createResponse()
            ->withHeader(HeaderName::CONTENT_TYPE, $responseContentType)
            ->withBody($responseBody);
    }
}
