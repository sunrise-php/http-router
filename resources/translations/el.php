<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Το URI του αιτήματος είναι κατεστραμμένο και δεν μπορεί να γίνει αποδεκτό από τον διακομιστή.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Ο πόρος που ζητήθηκε δεν βρέθηκε για αυτό το URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Η ζητούμενη μέθοδος δεν επιτρέπεται για αυτό το πόρο.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Ο τύπος μέσων του αιτήματος λείπει.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Ο τύπος μέσων του αιτήματος δεν υποστηρίζεται από αυτό το πόρο.',
    ErrorMessage::INVALID_VARIABLE => 'Η τιμή της μεταβλητής {{{ variable_name }}} στο URI του αιτήματος "{{ route_uri }}" είναι άκυρη.',
    ErrorMessage::INVALID_QUERY => 'Οι παράμετροι ερωτήματος του αιτήματος είναι άκυρες.',
    ErrorMessage::MISSING_HEADER => 'Η κεφαλίδα αιτήματος "{{ header_name }}" λείπει.',
    ErrorMessage::INVALID_HEADER => 'Η κεφαλίδα αιτήματος "{{ header_name }}" είναι άκυρη.',
    ErrorMessage::MISSING_COOKIE => 'Το cookie "{{ cookie_name }}" λείπει.',
    ErrorMessage::INVALID_COOKIE => 'Το cookie "{{ cookie_name }}" είναι άκυρο.',
    ErrorMessage::INVALID_BODY => 'Το σώμα του αιτήματος είναι άκυρο.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Κάτι πήγε στραβά.',
];
