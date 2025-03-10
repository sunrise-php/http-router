<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Message\StreamFactory;
use Sunrise\Http\Router\OpenApi\Controller\SwaggerController;
use Sunrise\Http\Router\OpenApi\SwaggerConfiguration;

final class SwaggerControllerTest extends TestCase
{
    public function testHandle(): void
    {
        $controller = new SwaggerController(
            swaggerConfiguration: new SwaggerConfiguration(),
            responseFactory: new ResponseFactory(),
            streamFactory: new StreamFactory(),
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $controller->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }
}
