<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'A URI da solicitação está malformada e não pode ser aceita pelo servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'O recurso solicitado não foi encontrado para esta URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'O método solicitado não é permitido para este recurso.',
    ErrorMessage::INVALID_VARIABLE => 'O valor da variável {{{ variable_name }}} na URI da solicitação "{{ route_uri }}" é inválido.',
    ErrorMessage::INVALID_QUERY => 'Os parâmetros da solicitação são inválidos.',
    ErrorMessage::MISSING_HEADER => 'O cabeçalho da solicitação "{{ header_name }}" está ausente.',
    ErrorMessage::INVALID_HEADER => 'O cabeçalho da solicitação "{{ header_name }}" é inválido.',
    ErrorMessage::MISSING_COOKIE => 'O cookie "{{ cookie_name }}" está ausente.',
    ErrorMessage::INVALID_COOKIE => 'O cookie "{{ cookie_name }}" é inválido.',
    ErrorMessage::INVALID_BODY => 'O corpo da solicitação é inválido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ocorreu um erro.',
];
