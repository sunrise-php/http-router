<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Http\Router\Exception\Http\HttpExceptionInterface;

/**
 * JsonErrorHandlingMiddleware
 *
 * @since 3.0.0
 */
final class JsonErrorHandlingMiddleware implements MiddlewareInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(ResponseFactoryInterface $responseFactory, LoggerInterface $logger)
    {
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpExceptionInterface $e) {
            $this->logger->debug($e->getMessage());

            return $this->responseFactory->createResponse($e->getStatusCode());
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());

            return $this->responseFactory->createResponse(500);
        }
    }
}
