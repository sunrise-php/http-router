<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'अनुरोध URI विकृत है और सर्वर द्वारा स्वीकार नहीं किया जा सकता है।',
    ErrorMessage::RESOURCE_NOT_FOUND => 'इस URI के लिए अनुरोधित संसाधन नहीं मिला।',
    ErrorMessage::METHOD_NOT_ALLOWED => 'इस संसाधन के लिए अनुरोधित विधि की अनुमति नहीं है।',
    ErrorMessage::MISSING_MEDIA_TYPE => 'अनुरोध मीडिया प्रकार गायब है।',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'इस संसाधन द्वारा अनुरोध मीडिया प्रकार का समर्थन नहीं किया जाता है।',
    ErrorMessage::INVALID_VARIABLE => 'अनुरोध URI "{{ route_uri }}" में चर {{{ variable_name }}} का मान अवैध है।',
    ErrorMessage::INVALID_QUERY => 'अनुरोध क्वेरी पैरामीटर अवैध हैं।',
    ErrorMessage::MISSING_HEADER => 'अनुरोध हेडर "{{ header_name }}" गायब है।',
    ErrorMessage::INVALID_HEADER => 'अनुरोध हेडर "{{ header_name }}" अवैध है।',
    ErrorMessage::MISSING_COOKIE => 'कुकी "{{ cookie_name }}" गायब है।',
    ErrorMessage::INVALID_COOKIE => 'कुकी "{{ cookie_name }}" अवैध है।',
    ErrorMessage::INVALID_BODY => 'अनुरोध बॉडी अवैध है।',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'कुछ गलत हो गया।',
];
