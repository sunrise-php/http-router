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
use Sunrise\Http\Router\ServerRequest;

/**
 * ClientIpAddressMiddleware
 *
 * @since 3.0.0
 */
final class ClientIpAddressMiddleware implements MiddlewareInterface
{

    /**
     * @var array<string, string>
     */
    private array $proxyChain;

    /**
     * Constructor of the class
     *
     * @param array<string, string> $proxyChain
     */
    public function __construct(array $proxyChain = [])
    {
        $this->proxyChain = $proxyChain;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = ServerRequest::from($request)->getClientIpAddress($this->proxyChain);

        return $handler->handle($request->withAttribute('@clientIp', $clientIp));
    }
}
