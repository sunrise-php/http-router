<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Sunrise\Coder\Codec\JsonCodec;
use Sunrise\Coder\CodecManager;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Message\StreamFactory;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\OpenApi\Controller\OpenApiController;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManager;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherManager;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManager;
use Sunrise\Http\Router\RequestHandlerReflector;

use function chmod;
use function sys_get_temp_dir;
use function touch;
use function unlink;

final class OpenApiControllerTest extends TestCase
{
    public function testHandle(): void
    {
        $openApiConfiguration = self::createOpenApiConfiguration();
        $openApiDocumentManager = self::createOpenApiDocumentManager($openApiConfiguration);
        $openApiDocumentManager->saveDocument(['foo' => 'bar']);

        $response = $this->createOpenApiController($openApiConfiguration, $openApiDocumentManager)
            ->handle($this->createMock(ServerRequestInterface::class));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        self::assertJsonStringEqualsJsonFile($openApiConfiguration->getDocumentFilename(), (string) $response->getBody());
    }

    public function testFailsWhenFileIsNotReadable(): void
    {
        $documentFilename = sys_get_temp_dir() . '/44b56c7d-5a53-4c47-8631-9a813458534e';
        touch($documentFilename);
        chmod($documentFilename, 0000);

        $openApiConfiguration = self::createOpenApiConfiguration(documentFilename: $documentFilename);
        $openApiDocumentManager = self::createOpenApiDocumentManager($openApiConfiguration);
        $openApiController = $this->createOpenApiController($openApiConfiguration, $openApiDocumentManager);

        $serverRequest = $this->createMock(ServerRequestInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The OpenAPI document was not saved or is unavailable.');

        try {
            $openApiController->handle($serverRequest);
        } finally {
            chmod($documentFilename, 0644);
            unlink($documentFilename);
        }
    }

    private function createOpenApiController(
        OpenApiConfiguration $openApiConfiguration,
        OpenApiDocumentManagerInterface $openApiDocumentManager,
    ): OpenApiController {
        return new OpenApiController(
            openApiConfiguration: $openApiConfiguration,
            openApiDocumentManager: $openApiDocumentManager,
            responseFactory: new ResponseFactory(),
            streamFactory: new StreamFactory(),
        );
    }

    private static function createOpenApiConfiguration(?string $documentFilename = null): OpenApiConfiguration
    {
        return new OpenApiConfiguration(
            initialDocument: [],
            initialOperation: [],
            documentMediaType: MediaType::JSON,
            documentFilename: $documentFilename,
        );
    }

    private static function createOpenApiDocumentManager(OpenApiConfiguration $openApiConfiguration): OpenApiDocumentManagerInterface
    {
        return new OpenApiDocumentManager(
            openApiConfiguration: $openApiConfiguration,
            openApiPhpTypeSchemaResolverManager: $openApiPhpTypeSchemaResolverManager = new OpenApiPhpTypeSchemaResolverManager($openApiConfiguration),
            openApiOperationEnricherManager: new OpenApiOperationEnricherManager($openApiConfiguration, $openApiPhpTypeSchemaResolverManager),
            requestHandlerReflector: new RequestHandlerReflector(),
            codecManager: new CodecManager([new JsonCodec()]),
        );
    }
}
