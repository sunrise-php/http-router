<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Annotation\GetRoute;

#[GetRoute(name: 'home', path: '/')]
final class HomeController extends AbstractController implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new ResponseFactory())->createResponse();
    }
}
