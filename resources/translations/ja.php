<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'リクエストURIは不正で、サーバーが受け付けることができません。',
    ErrorMessage::RESOURCE_NOT_FOUND => '要求されたリソースはこのURIに対して見つかりませんでした。',
    ErrorMessage::METHOD_NOT_ALLOWED => '要求されたメソッドはこのリソースに対して許可されていません。',
    ErrorMessage::INVALID_VARIABLE => 'リクエストURI「{{ route_uri }}」の変数{{{ variable_name }}}の値が無効です。',
    ErrorMessage::INVALID_QUERY => 'リクエストパラメータは無効です。',
    ErrorMessage::MISSING_HEADER => 'リクエストヘッダー「{{ header_name }}」がありません。',
    ErrorMessage::INVALID_HEADER => 'リクエストヘッダー「{{ header_name }}」は無効です。',
    ErrorMessage::MISSING_COOKIE => 'クッキー「{{ cookie_name }}」がありません。',
    ErrorMessage::INVALID_COOKIE => 'クッキー「{{ cookie_name }}」は無効です。',
    ErrorMessage::INVALID_BODY => 'リクエストボディは無効です。',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'エラーが発生しました。',
];
