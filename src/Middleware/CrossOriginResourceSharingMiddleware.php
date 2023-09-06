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

use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Cross-origin resource sharing
 *
 * @link https://github.com/neomerx/cors-psr7
 *
 * @since 3.0.0
 */
final class CrossOriginResourceSharingMiddleware implements MiddlewareInterface
{

    /**
     * Constructor of the class
     *
     * @param AnalyzerInterface $requestAnalyzer
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        private AnalyzerInterface $requestAnalyzer,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestAnalysisResult = $this->requestAnalyzer->analyze($request);

        switch ($requestAnalysisResult->getRequestType()) {
            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                return $handler->handle($request);

            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                return $this->responseFactory->createResponse(403);

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                $response = $this->responseFactory->createResponse(200);
                /** @var array<string, string> $preflightHeaders */
                $preflightHeaders = $requestAnalysisResult->getResponseHeaders();
                foreach ($preflightHeaders as $fieldName => $fieldValue) {
                    $response = $response->withHeader($fieldName, $fieldValue);
                }

                return $response;

            default:
                $response = $handler->handle($request);
                /** @var array<string, string> $preflightHeaders */
                $preflightHeaders = $requestAnalysisResult->getResponseHeaders();
                foreach ($preflightHeaders as $fieldName => $fieldValue) {
                    $response = $response->withHeader($fieldName, $fieldValue);
                }

                return $response;
        }
    }
}
