<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi\Command;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\CodecManager;
use Sunrise\Http\Router\Codec\JsonCodec;
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

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class RouterOpenApiBuildDocumentCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $router = new Router(
            referenceResolver: ReferenceResolver::build(),
            loaders: [
                new DescriptorLoader([
                    __DIR__ . '/../../Fixture/App/Controller',
                ]),
            ],
        );

        $openapiConfiguration = new OpenApiConfiguration(
            initialDocument: [
                'openapi' => OpenApiConfiguration::VERSION,
                'info' => [
                    'title' => 'API',
                    'version' => '1.0.0',
                ],
            ],
            initialOperation: [

            ],
            documentMediaType: MediaType::JSON,
            documentEncodingContext: [
                JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ],
        );

        $openapiDocumentManager = new OpenApiDocumentManager(
            $openapiConfiguration,
            $openapiPhpTypeSchemaResolverManager = new OpenApiPhpTypeSchemaResolverManager($openapiConfiguration),
            new OpenApiOperationEnricherManager($openapiConfiguration, $openapiPhpTypeSchemaResolverManager),
            new RequestHandlerReflector(),
            new CodecManager([new JsonCodec()]),
        );

        $command = new RouterOpenApiBuildDocumentCommand($router, $openapiDocumentManager);
        $commandTester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $commandTester->execute([]));

        self::assertJsonFileEqualsJsonFile(
            __DIR__ . '/../../Fixture/App/openapi.json',
            $openapiConfiguration->getDocumentFilename(),
        );
    }
}
