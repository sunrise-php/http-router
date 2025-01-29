<?php

declare(strict_types=1);

use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\RequestHandlerReflector;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;

use function DI\create;
use function DI\get;

return [
    'router.openapi.version' => '3.1.0',
    'router.openapi.initial_document' => [
        'openapi' => get('router.openapi.version'),
    ],
    'router.openapi.initial_operation' => [],
    'router.openapi.document_media_type' => OpenApiConfiguration::DEFAULT_DOCUMENT_MEDIA_TYPE,
    'router.openapi.document_encoding_context' => [],
    'router.openapi.document_filename' => null,
    'router.openapi.document_read_mode' => OpenApiConfiguration::DEFAULT_DOCUMENT_READ_MODE,
    'router.openapi.temporary_document_basename' => OpenApiConfiguration::DEFAULT_TEMPORARY_DOCUMENT_BASENAME,
    'router.openapi.default_timestamp_format' => OpenApiConfiguration::DEFAULT_TIMESTAMP_FORMAT,
    'router.openapi.empty_response_status_code' => OpenApiConfiguration::DEFAULT_EMPTY_RESPONSE_STATUS_CODE,
    'router.openapi.successful_response_status_code' => OpenApiConfiguration::DEFAULT_SUCCESSFUL_RESPONSE_STATUS_CODE,
    'router.openapi.successful_response_description' => OpenApiConfiguration::DEFAULT_SUCCESSFUL_RESPONSE_DESCRIPTION,
    'router.openapi.unsuccessful_response_view_name' => null,
    'router.openapi.unsuccessful_response_description' => OpenApiConfiguration::DEFAULT_UNSUCCESSFUL_RESPONSE_DESCRIPTION,

    OpenApiConfiguration::class => create()
        ->constructor(
            initialDocument: get('router.openapi.initial_document'),
            initialOperation: get('router.openapi.initial_operation'),
            documentMediaType: get('router.openapi.document_media_type'),
            documentEncodingContext: get('router.openapi.document_encoding_context'),
            documentFilename: get('router.openapi.document_filename'),
            documentReadMode: get('router.openapi.document_read_mode'),
            temporaryDocumentBasename: get('router.openapi.temporary_document_basename'),
            defaultTimestampFormat: get('router.openapi.default_timestamp_format'),
            emptyResponseStatusCode: get('router.openapi.empty_response_status_code'),
            successfulResponseStatusCode: get('router.openapi.successful_response_status_code'),
            successfulResponseDescription: get('router.openapi.successful_response_description'),
            unsuccessfulResponseViewName: get('router.openapi.unsuccessful_response_view_name'),
            unsuccessfulResponseDescription: get('router.openapi.unsuccessful_response_description'),
        ),

    RequestHandlerReflectorInterface::class => create(RequestHandlerReflector::class),
];
