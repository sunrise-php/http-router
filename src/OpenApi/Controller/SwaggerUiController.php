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
use Sunrise\Http\Router\Helper\TemplateRenderer;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Throwable;

/**
 * @since 3.0.0
 */
#[GetRoute(self::ROUTE_NAME, self::ROUTE_PATH)]
final class SwaggerUiController implements RequestHandlerInterface
{
    public const ROUTE_NAME = '@swagger-ui';
    public const ROUTE_PATH = '/swagger-ui.html';

    public const OPENAPI_URI_TEMPLATE_VAR_NAME = 'openapiUri';

    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $responseBody = $this->streamFactory->createStream(
            TemplateRenderer::renderTemplate(
                filename: $this->openApiConfiguration->swaggerUiTemplateFilename,
                variables: [
                    self::OPENAPI_URI_TEMPLATE_VAR_NAME => OpenApiController::ROUTE_PATH,
                ],
            ),
        );

        $responseContentType = 'text/html; charset=UTF-8';

        return $this->responseFactory->createResponse()
            ->withHeader(HeaderName::CONTENT_TYPE, $responseContentType)
            ->withBody($responseBody);
    }
}
