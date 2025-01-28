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
use Sunrise\Http\Router\Annotation\Priority;
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\Helper\TemplateRenderer;
use Sunrise\Http\Router\OpenApi\SwaggerConfiguration;
use Throwable;

/**
 * @since 3.0.0
 */
#[GetRoute(self::ROUTE_NAME, self::ROUTE_PATH)]
#[Priority(-1)]
final class SwaggerController implements RequestHandlerInterface
{
    public const ROUTE_NAME = '@swagger';
    public const ROUTE_PATH = '/swagger.html';

    public const CSS_URLS_VAR_NAME = 'css_urls';
    public const JS_URLS_VAR_NAME = 'js_urls';
    public const AUTO_RENDER_VAR_NAME = 'auto_render';
    public const OPENAPI_URI_VAR_NAME = 'openapi_uri';

    public function __construct(
        private readonly SwaggerConfiguration $swaggerConfiguration,
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
                filename: $this->swaggerConfiguration->templateFilename,
                variables: [
                    self::CSS_URLS_VAR_NAME => $this->swaggerConfiguration->cssUrls,
                    self::JS_URLS_VAR_NAME => $this->swaggerConfiguration->jsUrls,
                    self::AUTO_RENDER_VAR_NAME => $this->swaggerConfiguration->autoRender,
                    self::OPENAPI_URI_VAR_NAME => $this->swaggerConfiguration->openapiUri,
                ],
            ),
        );

        $responseContentType = 'text/html; charset=UTF-8';

        return $this->responseFactory->createResponse()
            ->withHeader(HeaderName::CONTENT_TYPE, $responseContentType)
            ->withBody($responseBody);
    }
}
