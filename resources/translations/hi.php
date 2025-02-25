<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'अनुरोध URI गलत स्वरूपित है और सर्वर द्वारा स्वीकार नहीं किया जा सकता।',
    ErrorMessage::RESOURCE_NOT_FOUND => 'इस URI के लिए अनुरोधित संसाधन नहीं मिला।',
    ErrorMessage::METHOD_NOT_ALLOWED => 'इस संसाधन के लिए अनुरोधित विधि की अनुमति नहीं है।',
    ErrorMessage::MISSING_MEDIA_TYPE => 'अनुरोध का मीडिया प्रकार गायब है।',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'इस संसाधन के लिए अनुरोध का मीडिया प्रकार समर्थित नहीं है।',
    ErrorMessage::INVALID_VARIABLE => 'अनुरोध URI \"{{ route_uri }}\" में चर {{{ variable_name }}} का मान अमान्य है।',
    ErrorMessage::INVALID_QUERY => 'अनुरोध के क्वेरी पैरामीटर अमान्य हैं।',
    ErrorMessage::MISSING_HEADER => 'अनुरोध हेडर \"{{ header_name }}\" गायब है।',
    ErrorMessage::INVALID_HEADER => 'अनुरोध हेडर \"{{ header_name }}\" अमान्य है।',
    ErrorMessage::MISSING_COOKIE => 'कुकी \"{{ cookie_name }}\" गायब है।',
    ErrorMessage::INVALID_COOKIE => 'कुकी \"{{ cookie_name }}\" अमान्य है।',
    ErrorMessage::INVALID_BODY => 'अनुरोध का बॉडी अमान्य है।',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'कुछ गलत हो गया।',
];
