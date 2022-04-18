<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BlankMiddleware extends AbstractMiddleware
{

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     *
     * @link https://www.php.net/manual/ru/language.oop5.magic.php#object.invoke
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $this->process($request, $handler)->withStatus(305);
    }
}
