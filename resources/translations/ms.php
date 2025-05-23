<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI permintaan adalah salah dan tidak dapat diterima oleh pelayan.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Sumber yang diminta tidak ditemukan untuk URI ini.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Kaedah yang diminta tidak dibenarkan untuk sumber ini.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Jenis media permintaan tidak ada.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Jenis media permintaan tidak disokong oleh sumber ini.',
    ErrorMessage::INVALID_VARIABLE => 'Nilai pembolehubah {{{ variable_name }}} dalam URI permintaan "{{ route_uri }}" tidak sah.',
    ErrorMessage::INVALID_QUERY => 'Parameter pertanyaan permintaan tidak sah.',
    ErrorMessage::MISSING_HEADER => 'Header permintaan "{{ header_name }}" tidak ada.',
    ErrorMessage::INVALID_HEADER => 'Header permintaan "{{ header_name }}" tidak sah.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" tidak ada.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" tidak sah.',
    ErrorMessage::INVALID_BODY => 'Badan permintaan tidak sah.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ada masalah sesuatu.',
];
