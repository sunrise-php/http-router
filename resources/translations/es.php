<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'El URI de la solicitud está mal formado y no puede ser aceptado por el servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'No se encontró el recurso solicitado para este URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El método solicitado no está permitido para este recurso.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Falta el tipo de medios de la solicitud.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'El tipo de medios de la solicitud no es compatible con este recurso.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} en el URI de la solicitud "{{ route_uri }}" es inválido.',
    ErrorMessage::INVALID_QUERY => 'Los parámetros de consulta de la solicitud son inválidos.',
    ErrorMessage::MISSING_HEADER => 'Falta el encabezado de la solicitud "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'El encabezado de la solicitud "{{ header_name }}" es inválido.',
    ErrorMessage::MISSING_COOKIE => 'Falta la cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'La cookie "{{ cookie_name }}" es inválida.',
    ErrorMessage::INVALID_BODY => 'El cuerpo de la solicitud es inválido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Algo salió mal.',
];
