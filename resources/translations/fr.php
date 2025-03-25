<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'L\'URI de la requête est mal formée et ne peut pas être acceptée par le serveur.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'La ressource demandée n\'a pas été trouvée pour cet URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'La méthode demandée n\'est pas autorisée pour cette ressource.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Le type de média de la requête est manquant.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Le type de média de la requête n\'est pas supporté par cette ressource.',
    ErrorMessage::INVALID_VARIABLE => 'La valeur de la variable {{{ variable_name }}} dans l\'URI de la requête "{{ route_uri }}" est invalide.',
    ErrorMessage::INVALID_QUERY => 'Les paramètres de la requête sont invalides.',
    ErrorMessage::MISSING_HEADER => 'L\'en-tête de la requête "{{ header_name }}" est manquant.',
    ErrorMessage::INVALID_HEADER => 'L\'en-tête de la requête "{{ header_name }}" est invalide.',
    ErrorMessage::MISSING_COOKIE => 'Le cookie "{{ cookie_name }}" est manquant.',
    ErrorMessage::INVALID_COOKIE => 'Le cookie "{{ cookie_name }}" est invalide.',
    ErrorMessage::INVALID_BODY => 'Le corps de la requête est invalide.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Une erreur est survenue.',
];
