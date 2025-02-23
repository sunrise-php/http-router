<?php

declare(strict_types=1);

use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManager;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherManager;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherManagerInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManager;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\SwaggerConfiguration;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;

use function DI\create;
use function DI\get;
use function DI\string;

return [
    'router.openapi.initial_document' => [
        'openapi' => OpenApiConfiguration::VERSION,
        'info' => get('router.openapi.initial_document.info'),
    ],

    'router.openapi.initial_document.info' => [
        'title' => string('{app.name}@{app.env}'),
        'version' => get('app.version'),
    ],

    'router.openapi.initial_operation' => [
    ],

    'router.openapi.document_media_type' => get('router.default_media_type'),
    'router.openapi.document_encoding_context' => [],
    'router.openapi.document_filename' => null,

    'router.openapi.default_timestamp_format' => OpenApiConfiguration::DEFAULT_TIMESTAMP_FORMAT,
    'router.openapi.default_response_description' => OpenApiConfiguration::DEFAULT_RESPONSE_DESCRIPTION,

    'router.openapi.php_type_schema_resolvers' => [],
    'router.openapi.operation_enrichers' => [],

    'router.swagger.template_filename' => SwaggerConfiguration::DEFAULT_TEMPLATE_FILENAME,
    'router.swagger.css_urls' => SwaggerConfiguration::DEFAULT_CSS_URLS,
    'router.swagger.js_urls' => SwaggerConfiguration::DEFAULT_JS_URLS,
    'router.swagger.openapi_uri' => SwaggerConfiguration::DEFAULT_OPENAPI_URI,
    'router.swagger.template_variables' => [],

    OpenApiConfiguration::class => create()
        ->constructor(
            initialDocument: get('router.openapi.initial_document'),
            initialOperation: get('router.openapi.initial_operation'),
            documentMediaType: get('router.openapi.document_media_type'),
            documentEncodingContext: get('router.openapi.document_encoding_context'),
            documentFilename: get('router.openapi.document_filename'),
            defaultTimestampFormat: get('router.openapi.default_timestamp_format'),
            defaultResponseDescription: get('router.openapi.default_response_description'),
        ),

    OpenApiPhpTypeSchemaResolverManagerInterface::class => create(OpenApiPhpTypeSchemaResolverManager::class)
        ->constructor(
            openApiConfiguration: get(OpenApiConfiguration::class),
            phpTypeSchemaResolvers: get('router.openapi.php_type_schema_resolvers'),
        ),

    OpenApiOperationEnricherManagerInterface::class => create(OpenApiOperationEnricherManager::class)
        ->constructor(
            openApiConfiguration: get(OpenApiConfiguration::class),
            openApiPhpTypeSchemaResolverManager: get(OpenApiPhpTypeSchemaResolverManagerInterface::class),
            operationEnrichers: get('router.openapi.operation_enrichers'),
        ),

    OpenApiDocumentManagerInterface::class => create(OpenApiDocumentManager::class)
        ->constructor(
            openApiConfiguration: get(OpenApiConfiguration::class),
            openApiPhpTypeSchemaResolverManager: get(OpenApiPhpTypeSchemaResolverManagerInterface::class),
            openApiOperationEnricherManager: get(OpenApiOperationEnricherManagerInterface::class),
            requestHandlerReflector: get(RequestHandlerReflectorInterface::class),
            codecManager: get(CodecManagerInterface::class),
        ),

    SwaggerConfiguration::class => create()
        ->constructor(
            templateFilename: get('router.swagger.template_filename'),
            cssUrls: get('router.swagger.css_urls'),
            jsUrls: get('router.swagger.js_urls'),
            openapiUri: get('router.swagger.openapi_uri'),
            templateVariables: get('router.swagger.template_variables'),
        ),
];
