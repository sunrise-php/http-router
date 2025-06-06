<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'URI permintaan salah format dan tidak dapat diterima oleh server.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'Sumber daya yang diminta tidak ditemukan untuk URI ini.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'Metode yang diminta tidak diizinkan untuk sumber daya ini.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'Tipe media permintaan hilang.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'Tipe media permintaan tidak didukung oleh sumber daya ini.',
    ErrorMessage::INVALID_VARIABLE => 'Nilai dari variabel {{{ variable_name }}} dalam URI permintaan "{{ route_uri }}" tidak valid.',
    ErrorMessage::INVALID_QUERY => 'Parameter kueri permintaan tidak valid.',
    ErrorMessage::MISSING_HEADER => 'Header permintaan "{{ header_name }}" hilang.',
    ErrorMessage::INVALID_HEADER => 'Header permintaan "{{ header_name }}" tidak valid.',
    ErrorMessage::MISSING_COOKIE => 'Cookie "{{ cookie_name }}" hilang.',
    ErrorMessage::INVALID_COOKIE => 'Cookie "{{ cookie_name }}" tidak valid.',
    ErrorMessage::INVALID_BODY => 'Isi permintaan tidak valid.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'Ada kesalahan internal.',
];
