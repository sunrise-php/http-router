<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'অনুরোধের URI ত্রুটিপূর্ণ এবং এটি সার্ভার দ্বারা গ্রহণযোগ্য নয়।',
    ErrorMessage::RESOURCE_NOT_FOUND => 'এই URI জন্য অনুরোধকৃত সম্পদ পাওয়া যায়নি।',
    ErrorMessage::METHOD_NOT_ALLOWED => 'এই সম্পদের জন্য অনুরোধকৃত পদ্ধতি অনুমোদিত নয়।',
    ErrorMessage::MISSING_MEDIA_TYPE => 'অনুরোধের মিডিয়া টাইপ অনুপস্থিত।',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'এই সম্পদের দ্বারা অনুরোধের মিডিয়া টাইপ সমর্থিত নয়।',
    ErrorMessage::INVALID_VARIABLE => 'অনুরোধের URI "{{ route_uri }}" তে ভেরিয়েবলের {{{ variable_name }}} এর মান অবৈধ।',
    ErrorMessage::INVALID_QUERY => 'অনুরোধের কুয়েরি প্যারামিটার অবৈধ।',
    ErrorMessage::MISSING_HEADER => 'অনুরোধের হেডার "{{ header_name }}" অনুপস্থিত।',
    ErrorMessage::INVALID_HEADER => 'অনুরোধের হেডার "{{ header_name }}" অবৈধ।',
    ErrorMessage::MISSING_COOKIE => 'কুকি "{{ cookie_name }}" অনুপস্থিত।',
    ErrorMessage::INVALID_COOKIE => 'কুকি "{{ cookie_name }}" অবৈধ।',
    ErrorMessage::INVALID_BODY => 'অনুরোধের বডি অবৈধ।',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'কিছু একটা ভুল হয়েছে।',
];
