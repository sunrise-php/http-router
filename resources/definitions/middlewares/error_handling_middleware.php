<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Coder\Codec\JsonCodec;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\Dictionary\MediaType;
use Sunrise\Coder\MediaTypeInterface;
use Sunrise\Http\Router\Dictionary\LanguageCode;
use Sunrise\Http\Router\Middleware\ErrorHandlingMiddleware;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\View\ErrorView;
use Sunrise\Translator\TranslatorManagerInterface;

use function DI\add;
use function DI\create;
use function DI\factory;
use function DI\get;

return [
    'router.error_handling_middleware.codec_context' => [
        JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_PARTIAL_OUTPUT_ON_ERROR,
    ],

    'router.error_handling_middleware.produced_media_types' => [],
    'router.error_handling_middleware.default_media_type' => MediaType::JSON,
    'router.error_handling_middleware.produced_languages' => [],
    'router.error_handling_middleware.default_language' => LanguageCode::English,
    'router.error_handling_middleware.fatal_error_status_code' => null,
    'router.error_handling_middleware.fatal_error_message' => null,

    'router.middlewares' => add([
        create(ErrorHandlingMiddleware::class)
            ->constructor(
                responseFactory: get(ResponseFactoryInterface::class),
                streamFactory: get(StreamFactoryInterface::class),
                codecManager: get(CodecManagerInterface::class),
                codecContext: get('router.error_handling_middleware.codec_context'),
                producedMediaTypes: get('router.error_handling_middleware.produced_media_types'),
                defaultMediaType: get('router.error_handling_middleware.default_media_type'),
                translatorManager: get(TranslatorManagerInterface::class),
                producedLanguages: get('router.error_handling_middleware.produced_languages'),
                defaultLanguage: get('router.error_handling_middleware.default_language'),
                logger: get(LoggerInterface::class),
                fatalErrorStatusCode: get('router.error_handling_middleware.fatal_error_status_code'),
                fatalErrorMessage: get('router.error_handling_middleware.fatal_error_message'),
            ),
    ]),

    'router.openapi.initial_operation' => add([
        'responses' => [
            'default' => [
                'description' => 'The operation was unsuccessful.',
                'content' => factory(
                    static function (ContainerInterface $container): array {
                        $mediaTypes = $container->get('router.error_handling_middleware.produced_media_types');
                        $mediaTypes[] = $container->get('router.error_handling_middleware.default_media_type');

                        $content = [];
                        /** @var MediaTypeInterface $mediaType */
                        foreach ($mediaTypes as $mediaType) {
                            $content[$mediaType->getIdentifier()] = [
                                'schema' => new Type(ErrorView::class),
                            ];
                        }

                        return $content;
                    },
                ),
            ],
        ],
    ]),
];
