<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI permintaan salah format dan tidak dapat diterima oleh server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Sumber daya yang diminta tidak ditemukan untuk URI ini.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Metode yang diminta tidak diperbolehkan untuk sumber daya ini.',
    ErrorMessage::INVALID_VARIABLE => 'Nilai variabel {{{ variable_name }}} dalam URI permintaan "{{ route_uri }}" tidak valid.',
    ErrorMessage::INVALID_QUERY => 'Parameter permintaan tidak valid.',
    ErrorMessage::MISSING_HEADER => 'Header permintaan "{{ header_name }}" hilang.',
    ErrorMessage::INVALID_HEADER => 'Header permintaan "{{ header_name }}" tidak valid.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" hilang.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" tidak valid.',
    ErrorMessage::INVALID_BODY => 'Badan permintaan tidak valid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Terjadi kesalahan.',
];
