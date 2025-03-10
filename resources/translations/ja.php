<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'リクエストURIの形式が正しくなく、サーバーで受け付けられません。',
    ErrorMessage::RESOURCE_NOT_FOUND => 'このURIに対して要求されたリソースが見つかりませんでした。',
    ErrorMessage::METHOD_NOT_ALLOWED => 'このリソースに対して要求されたメソッドは許可されていません。',
    ErrorMessage::MISSING_MEDIA_TYPE => 'リクエストのメディアタイプがありません。',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'このリソースはリクエストのメディアタイプをサポートしていません。',
    ErrorMessage::INVALID_VARIABLE => 'リクエストURI「{{ route_uri }}」内の変数 {{{ variable_name }}} の値が無効です。',
    ErrorMessage::INVALID_QUERY => 'リクエストのクエリパラメータが無効です。',
    ErrorMessage::MISSING_HEADER => 'リクエストヘッダー「{{ header_name }}」がありません。',
    ErrorMessage::INVALID_HEADER => 'リクエストヘッダー「{{ header_name }}」が無効です。',
    ErrorMessage::MISSING_COOKIE => 'クッキー「{{ cookie_name }}」がありません。',
    ErrorMessage::INVALID_COOKIE => 'クッキー「{{ cookie_name }}」が無効です。',
    ErrorMessage::INVALID_BODY => 'リクエストボディが無効です。',
    ErrorMessage::INTERNAL_SERVER_ERROR => '何かがうまくいきませんでした。',
];
