<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'La URI de la solicitud está malformada y no puede ser aceptada por el servidor.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'El recurso solicitado no fue encontrado para esta URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'El método solicitado no está permitido para este recurso.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Falta el tipo de medio de la solicitud.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'El tipo de medio de la solicitud no es compatible con este recurso.',
    ErrorMessage::INVALID_VARIABLE => 'El valor de la variable {{{ variable_name }}} en la URI de la solicitud \"{{ route_uri }}\" no es válido.',
    ErrorMessage::INVALID_QUERY => 'Los parámetros de consulta de la solicitud no son válidos.',
    ErrorMessage::MISSING_HEADER => 'Falta el encabezado de la solicitud \"{{ header_name }}\".',
    ErrorMessage::INVALID_HEADER => 'El encabezado de la solicitud \"{{ header_name }}\" no es válido.',
    ErrorMessage::MISSING_COOKIE => 'Falta la cookie \"{{ cookie_name }}\".',
    ErrorMessage::INVALID_COOKIE => 'La cookie \"{{ cookie_name }}\" no es válida.',
    ErrorMessage::INVALID_BODY => 'El cuerpo de la solicitud no es válido.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Algo salió mal.',
];
