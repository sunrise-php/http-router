<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'Η URI του αιτήματος είναι κακώς μορφοποιημένη και δεν μπορεί να γίνει αποδεκτή από τον διακομιστή.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Ο ζητούμενος πόρος δεν βρέθηκε για αυτήν την URI.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Η ζητούμενη μέθοδος δεν επιτρέπεται για αυτόν τον πόρο.',
    ErrorMessage::INVALID_VARIABLE => 'Η τιμή της μεταβλητής {{{ variable_name }}} στην URI του αιτήματος "{{ route_uri }}" είναι άκυρη.',
    ErrorMessage::INVALID_QUERY => 'Οι παράμετροι του αιτήματος είναι άκυροι.',
    ErrorMessage::MISSING_HEADER => 'Λείπει το header του αιτήματος "{{ header_name }}".',
    ErrorMessage::INVALID_HEADER => 'Το header του αιτήματος "{{ header_name }}" είναι άκυρο.',
    ErrorMessage::MISSING_COOKIE => 'Λείπει το cookie "{{ cookie_name }}".',
    ErrorMessage::INVALID_COOKIE => 'Το cookie "{{ cookie_name }}" είναι άκυρο.',
    ErrorMessage::INVALID_BODY => 'Το σώμα του αιτήματος είναι άκυρο.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Προέκυψε σφάλμα.',
];
