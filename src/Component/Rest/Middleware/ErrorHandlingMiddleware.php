<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Component\Rest\Middleware;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\Http\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\Http\HttpMethodNotAllowedException;
use Sunrise\Http\Router\Exception\Http\HttpUnsupportedMediaTypeException;
use Sunrise\Http\Router\Exception\UnprocessableEntityException;

/**
 * ErrorHandlingMiddleware
 *
 * @since 3.0.0
 */
final class ErrorHandlingMiddleware implements MiddlewareInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpMethodNotAllowedException $e) {
            /** @psalm-suppress TooFewArguments, MixedArgument */
            return $this->responseFactory->createResponse($e->getStatusCode())
                ->withHeader(...$e->getAllowHeaderArguments());
        } catch (HttpUnsupportedMediaTypeException $e) {
            /** @psalm-suppress TooFewArguments, MixedArgument */
            return $this->responseFactory->createResponse($e->getStatusCode())
                ->withHeader(...$e->getAcceptHeaderArguments());
        } catch (UnprocessableEntityException $e) {
            return $this->responseFactory->createResponse($e->getStatusCode());
        } catch (HttpExceptionInterface $e) {
            return $this->responseFactory->createResponse($e->getStatusCode());
        }
    }
}
