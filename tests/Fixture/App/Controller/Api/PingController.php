<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Annotation\GetApiRoute;

#[GetApiRoute('ping', '/ping')]
final class PingController extends AbstractController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new ResponseFactory())->createResponse();
    }
}
