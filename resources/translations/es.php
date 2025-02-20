<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'La URI de la solicitud está mal formada y no puede ser aceptada por el servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'El recurso solicitado no se encontró para esta URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El método solicitado no está permitido para este recurso.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} en la URI de la solicitud "{{ route_uri }}" no es válido.',
    ErrorMessage::INVALID_QUERY => 'Los parámetros de la solicitud son inválidos.',
    ErrorMessage::MISSING_HEADER => 'Falta el encabezado de la solicitud "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'El encabezado de la solicitud "{{ header_name }}" es inválido.',
    ErrorMessage::MISSING_COOKIE => 'Falta la cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'La cookie "{{ cookie_name }}" es inválida.',
    ErrorMessage::INVALID_BODY => 'El cuerpo de la solicitud es inválido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ocurrió un error.',
];
