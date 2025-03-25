<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'リクエストURIが不正であり、サーバーで受け入れることができません。',
    ErrorMessage::RESOURCE_NOT_FOUND => 'このURIに対して要求されたリソースが見つかりませんでした。',
    ErrorMessage::METHOD_NOT_ALLOWED => '要求されたメソッドはこのリソースに対して許可されていません。',
    ErrorMessage::MISSING_MEDIA_TYPE => 'リクエストのメディアタイプが欠落しています。',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'リクエストのメディアタイプはこのリソースではサポートされていません。',
    ErrorMessage::INVALID_VARIABLE => 'リクエストURI "{{ route_uri }}" の変数 {{{ variable_name }}} の値が無効です。',
    ErrorMessage::INVALID_QUERY => 'リクエストクエリパラメータが無効です。',
    ErrorMessage::MISSING_HEADER => 'リクエストヘッダー "{{ header_name }}" が欠落しています。',
    ErrorMessage::INVALID_HEADER => 'リクエストヘッダー "{{ header_name }}" が無効です。',
    ErrorMessage::MISSING_COOKIE => 'クッキー "{{ cookie_name }}" が欠落しています。',
    ErrorMessage::INVALID_COOKIE => 'クッキー "{{ cookie_name }}" が無効です。',
    ErrorMessage::INVALID_BODY => 'リクエストボディが無効です。',
    ErrorMessage::INTERNAL_SERVER_ERROR => '何かがうまくいきませんでした。',
];
