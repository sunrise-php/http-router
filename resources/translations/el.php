<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Το URI του αιτήματος είναι εσφαλμένο και δεν μπορεί να γίνει αποδεκτό από τον διακομιστή.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Ο ζητούμενος πόρος δεν βρέθηκε για αυτό το URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Η ζητούμενη μέθοδος δεν επιτρέπεται για αυτόν τον πόρο.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Ο τύπος μέσου του αιτήματος λείπει.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Ο τύπος μέσου του αιτήματος δεν υποστηρίζεται για αυτόν τον πόρο.',
    ErrorMessage::INVALID_VARIABLE => 'Η τιμή της μεταβλητής {{{ variable_name }}} στο URI του αιτήματος \"{{ route_uri }}\" είναι μη έγκυρη.',
    ErrorMessage::INVALID_QUERY => 'Οι παράμετροι του ερωτήματος του αιτήματος είναι μη έγκυρες.',
    ErrorMessage::MISSING_HEADER => 'Το header του αιτήματος \"{{ header_name }}\" λείπει.',
    ErrorMessage::INVALID_HEADER => 'Το header του αιτήματος \"{{ header_name }}\" είναι μη έγκυρο.',
    ErrorMessage::MISSING_COOKIE => 'Το cookie \"{{ cookie_name }}\" λείπει.',
    ErrorMessage::INVALID_COOKIE => 'Το cookie \"{{ cookie_name }}\" είναι μη έγκυρο.',
    ErrorMessage::INVALID_BODY => 'Το σώμα του αιτήματος είναι μη έγκυρο.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Κάτι πήγε στραβά.',
];
