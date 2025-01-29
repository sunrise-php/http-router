<?php

declare(strict_types=1);

use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\RequestHandlerReflector;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;

use function DI\create;
use function DI\get;

return [
    'router.openapi.initial_document' => [],
    'router.openapi.initial_operation' => [],

    'router.openapi.document_media_type' => OpenApiConfiguration::DEFAULT_DOCUMENT_MEDIA_TYPE,
    'router.openapi.document_encoding_context' => [],
    'router.openapi.document_filename' => null,

    'router.openapi.default_timestamp_format' => OpenApiConfiguration::DEFAULT_TIMESTAMP_FORMAT,
    'router.openapi.response_description' => OpenApiConfiguration::DEFAULT_SUCCESSFUL_RESPONSE_DESCRIPTION,

    OpenApiConfiguration::class => create()
        ->constructor(
            initialDocument: get('router.openapi.initial_document'),
            initialOperation: get('router.openapi.initial_operation'),
            documentMediaType: get('router.openapi.document_media_type'),
            documentEncodingContext: get('router.openapi.document_encoding_context'),
            documentFilename: get('router.openapi.document_filename'),
            defaultTimestampFormat: get('router.openapi.default_timestamp_format'),
            responseDescription: get('router.openapi.successful_response_description'),
        ),

    RequestHandlerReflectorInterface::class => create(RequestHandlerReflector::class),
];
