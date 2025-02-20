<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'आवेदन URI गलत तरीके से स्वरूपित है और इसे सर्वर द्वारा स्वीकार नहीं किया जा सकता।',
    ErrorMessage::RESOURCE_NOT_FOUND => 'इस URI के लिए अनुरोधित संसाधन नहीं मिला।',
    ErrorMessage::METHOD_NOT_ALLOWED => 'इस संसाधन के लिए अनुरोधित विधि अनुमति प्राप्त नहीं है।',
    ErrorMessage::INVALID_VARIABLE => 'अनुरोध URI "{{ route_uri }}" में {{{ variable_name }}} नामक वेरिएबल का मान अमान्य है।',
    ErrorMessage::INVALID_QUERY => 'अनुरोध पैरामीटर अमान्य हैं।',
    ErrorMessage::MISSING_HEADER => 'अनुरोध हेडर "{{ header_name }}" गायब है।',
    ErrorMessage::INVALID_HEADER => 'अनुरोध हेडर "{{ header_name }}" अमान्य है।',
    ErrorMessage::MISSING_COOKIE => 'कुकी "{{ cookie_name }}" गायब है।',
    ErrorMessage::INVALID_COOKIE => 'कुकी "{{ cookie_name }}" अमान्य है।',
    ErrorMessage::INVALID_BODY => 'अनुरोध बॉडी अमान्य है।',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'कुछ गलत हो गया।',
];
