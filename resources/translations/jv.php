<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI panjalukan rusak lan ora bisa ditampa dening server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Sumber daya sing dijaluk ora ditemokake kanggo URI iki.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Metode sing dijaluk ora diidini kanggo sumber daya iki.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Tipe media panjalukan ilang.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Tipe media panjalukan ora didhukung dening sumber daya iki.',
    ErrorMessage::INVALID_VARIABLE => 'Nilai saka variabel {{{ variable_name }}} ing URI panjalukan "{{ route_uri }}" ora sah.',
    ErrorMessage::INVALID_QUERY => 'Parameter query panjalukan ora sah.',
    ErrorMessage::MISSING_HEADER => 'Header panjalukan "{{ header_name }}" ilang.',
    ErrorMessage::INVALID_HEADER => 'Header panjalukan "{{ header_name }}" ora sah.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" ilang.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" ora sah.',
    ErrorMessage::INVALID_BODY => 'Badan panjalukan ora sah.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ana sing salah.',
];
