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

/**
 * @since 3.0.0
 */
#[GetRoute(self::ROUTE_NAME, self::ROUTE_PATH)]
final class SwaggerUiController implements RequestHandlerInterface
{
    public const ROUTE_NAME = '@swagger-ui';
    public const ROUTE_PATH = '/swagger.html';

    private const CONTENT_TYPE = 'text/html; charset=UTF-8';
    private const TEMPLATE_FILENAME = __DIR__ . '/../../../resources/templates/swagger-ui.phtml';

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->streamFactory->createStream(
            TemplateRenderer::renderTemplate(
                filename: self::TEMPLATE_FILENAME,
                variables: [
                    'openapiDocumentUri' => OpenApiController::ROUTE_PATH,
                ],
            ),
        );

        return $this->responseFactory->createResponse()
            ->withHeader(HeaderName::CONTENT_TYPE, self::CONTENT_TYPE)
            ->withBody($body);
    }
}
