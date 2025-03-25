<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'ਬੇਨਤੀ URI ਮਾਲਫਾਰਮਡ ਹੈ ਅਤੇ ਸਰਵਰ ਦੁਆਰਾ ਸਵੀਕਾਰ ਨਹੀਂ ਕੀਤਾ ਜਾ ਸਕਦਾ।',
    ErrorMessage::RESOURCE_NOT_FOUND => 'ਮੰਗੇ ਗਏ ਸਰੋਤ ਇਸ URI ਲਈ ਨਹੀਂ ਮਿਲਿਆ।',
    ErrorMessage::METHOD_NOT_ALLOWED => 'ਮੰਗੀ ਗਈ ਵਿਧਿ ਇਸ ਸਰੋਤ ਲਈ ਮਨਜ਼ੂਰ ਨਹੀਂ ਹੈ।',
    ErrorMessage::MISSING_MEDIA_TYPE => 'ਬੇਨਤੀ ਮੀਡੀਆ ਕਿਸਮ ਲਾਪਤਾ ਹੈ।',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'ਬੇਨਤੀ ਮੀਡੀਆ ਕਿਸਮ ਇਸ ਸਰੋਤ ਦੁਆਰਾ ਸਮਰਥਿਤ ਨਹੀਂ ਹੈ।',
    ErrorMessage::INVALID_VARIABLE => 'ਬੇਨਤੀ URI "{{ route_uri }}" ਵਿੱਚ ਤਬਦੀਲੀ {{{ variable_name }}} ਦੀ ਕਦਰ ਗਲਤ ਹੈ।',
    ErrorMessage::INVALID_QUERY => 'ਬੇਨਤੀ ਕੁਐਰੀ ਪੈਰਾਮੀਟਰ ਗਲਤ ਹਨ।',
    ErrorMessage::MISSING_HEADER => 'ਬੇਨਤੀ ਹੈਡਰ "{{ header_name }}" ਲਾਪਤਾ ਹੈ।',
    ErrorMessage::INVALID_HEADER => 'ਬੇਨਤੀ ਹੈਡਰ "{{ header_name }}" ਗਲਤ ਹੈ।',
    ErrorMessage::MISSING_COOKIE => 'ਕੂਕੀ "{{ cookie_name }}" ਲਾਪਤਾ ਹੈ।',
    ErrorMessage::INVALID_COOKIE => 'ਕੂਕੀ "{{ cookie_name }}" ਗਲਤ ਹੈ।',
    ErrorMessage::INVALID_BODY => 'ਬੇਨਤੀ ਬਾਡੀ ਗਲਤ ਹੈ।',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'ਕੁਝ ਤਕਨੀਕੀ ਗਲਤੀ ਹੋ ਗਈ।',
];
