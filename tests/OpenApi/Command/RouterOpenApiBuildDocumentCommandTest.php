<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi\Command;

use ErrorException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sunrise\Coder\Codec\JsonCodec;
use Sunrise\Coder\CodecManager;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\OpenApi\Command\RouterOpenApiBuildDocumentCommand;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManager;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherManager;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManager;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\RequestHandlerReflector;
use Sunrise\Http\Router\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function chmod;
use function mkdir;
use function restore_error_handler;
use function rmdir;
use function set_error_handler;
use function sys_get_temp_dir;
use function touch;
use function unlink;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class RouterOpenApiBuildDocumentCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $openApiConfiguration = self::createOpenApiConfiguration();
        $routerOpenApiBuildDocumentCommand = self::createRouterOpenApiBuildDocumentCommand($openApiConfiguration);

        $commandTester = new CommandTester($routerOpenApiBuildDocumentCommand);
        self::assertSame(Command::SUCCESS, $commandTester->execute([]));

        self::assertJsonFileEqualsJsonFile(
            __DIR__ . '/../../Fixture/App/openapi.json',
            $openApiConfiguration->getDocumentFilename(),
        );
    }

    public function testFailsWhenDirIsNotWritable(): void
    {
        $documentDirname = sys_get_temp_dir() . '/8d3a6fc8-835e-4dcc-a893-88169de702c4';
        mkdir($documentDirname, 0555);

        $documentFilename = $documentDirname . '/d9ff8748-eba0-4674-8115-c88568247140';

        $openApiConfiguration = self::createOpenApiConfiguration(documentFilename: $documentFilename);
        $routerOpenApiBuildDocumentCommand = self::createRouterOpenApiBuildDocumentCommand($openApiConfiguration);
        $commandTester = new CommandTester($routerOpenApiBuildDocumentCommand);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The directory for the OpenAPI document is not writable.');

        try {
            $commandTester->execute([]);
        } finally {
            chmod($documentDirname, 0755);
            rmdir($documentDirname);
        }
    }

    public function testFailsWhenFileIsNotWritable(): void
    {
        $documentFilename = sys_get_temp_dir() . '/49860707-d753-4d09-b6ba-4b0abf997426';
        touch($documentFilename);
        chmod($documentFilename, 0444);

        $openApiConfiguration = self::createOpenApiConfiguration(documentFilename: $documentFilename);
        $routerOpenApiBuildDocumentCommand = self::createRouterOpenApiBuildDocumentCommand($openApiConfiguration);
        $commandTester = new CommandTester($routerOpenApiBuildDocumentCommand);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The OpenAPI document could not be saved.');

        set_error_handler(static function ($severity, $message, $filename, $line): never {
            throw new ErrorException($message, 0, $severity, $filename, $line);
        });

        try {
            $commandTester->execute([]);
        } finally {
            restore_error_handler();
            chmod($documentFilename, 0644);
            unlink($documentFilename);
        }
    }

    private static function createOpenApiConfiguration(?string $documentFilename = null): OpenApiConfiguration
    {
        return new OpenApiConfiguration(
            initialDocument: [
                'openapi' => OpenApiConfiguration::VERSION,
                'info' => [
                    'title' => 'API',
                    'version' => '1.0.0',
                ],
            ],
            initialOperation: [
                'externalDocs' => [
                    'description' => 'Find more info here',
                    'url' => 'https://example.com',
                ],
            ],
            documentMediaType: MediaType::JSON,
            documentEncodingContext: [
                JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ],
            documentFilename: $documentFilename,
        );
    }

    private static function createRouterOpenApiBuildDocumentCommand(?OpenApiConfiguration $openApiConfiguration = null): RouterOpenApiBuildDocumentCommand
    {
        $router = new Router(
            referenceResolver: ReferenceResolver::build(),
            loaders: [
                new DescriptorLoader([
                    __DIR__ . '/../../Fixture/App/Controller',
                ]),
            ],
        );

        $openApiDocumentManager = new OpenApiDocumentManager(
            openApiConfiguration: $openApiConfiguration ??= self::createOpenApiConfiguration(),
            openApiPhpTypeSchemaResolverManager: $openApiPhpTypeSchemaResolverManager = new OpenApiPhpTypeSchemaResolverManager($openApiConfiguration),
            openApiOperationEnricherManager: new OpenApiOperationEnricherManager($openApiConfiguration, $openApiPhpTypeSchemaResolverManager),
            requestHandlerReflector: new RequestHandlerReflector(),
            codecManager: new CodecManager([new JsonCodec()]),
        );

        return new RouterOpenApiBuildDocumentCommand($router, $openApiDocumentManager);
    }
}
