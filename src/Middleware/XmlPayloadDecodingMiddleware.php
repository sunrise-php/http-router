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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\InvalidRequestPayloadException;
use Sunrise\Http\Router\ServerRequest;
use SimpleXMLElement;
use Throwable;

/**
 * Import functions
 */
use function sprintf;

/**
 * Import constants
 */
use const LIBXML_COMPACT;
use const LIBXML_NONET;
use const LIBXML_NOERROR;
use const LIBXML_NOWARNING;
use const LIBXML_PARSEHUGE;

/**
 * XmlPayloadDecodingMiddleware
 *
 * @since 3.0.0
 */
final class XmlPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (ServerRequest::from($request)->isXml()) {
            $request = $request->withParsedBody(
                $this->decodeRequestPayload($request)
            );
        }

        return $handler->handle($request);
    }

    /**
     * Tries to decode the given request's payload
     *
     * @param ServerRequestInterface $request
     *
     * @return SimpleXMLElement
     *
     * @throws InvalidRequestPayloadException
     *         If the request's payload cannot be decoded.
     */
    private function decodeRequestPayload(ServerRequestInterface $request): SimpleXMLElement
    {
        // https://www.php.net/manual/en/libxml.constants.php
        $options = LIBXML_COMPACT | LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE;

        try {
            $result = new SimpleXMLElement($request->getBody()->__toString(), $options);
        } catch (Throwable $e) {
            throw new InvalidRequestPayloadException(sprintf('Invalid Payload: %s', $e->getMessage()), 0, $e);
        }

        return $result;
    }
}
