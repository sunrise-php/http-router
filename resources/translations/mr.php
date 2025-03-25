<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'विनंती URI चुकीचा आहे आणि सर्व्हरद्वारे स्वीकारला जाऊ शकत नाही.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'या URI साठी विनंती केलेला संसाधन सापडला नाही.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'या संसाधनासाठी विनंती केलेली पद्धत अनुमत नाही.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'विनंती मीडिया प्रकार गहाळ आहे.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'या संसाधनाद्वारे विनंती मीडिया प्रकार समर्थित नाही.',
    ErrorMessage::INVALID_VARIABLE => 'विनंती URI "{{ route_uri }}" मध्ये {{{ variable_name }}} या चलाचे मूल्य अवैध आहे.',
    ErrorMessage::INVALID_QUERY => 'विनंती क्वेरी पॅरामीटर्स अवैध आहेत.',
    ErrorMessage::MISSING_HEADER => 'विनंती शीर्षलेख "{{ header_name }}" अनुपस्थित आहे.',
    ErrorMessage::INVALID_HEADER => 'विनंती शीर्षलेख "{{ header_name }}" अवैध आहे.',
    ErrorMessage::MISSING_COOKIE => 'कुकी "{{ cookie_name }}" अनुपस्थित आहे.',
    ErrorMessage::INVALID_COOKIE => 'कुकी "{{ cookie_name }}" अवैध आहे.',
    ErrorMessage::INVALID_BODY => 'विनंतीची बॉडी अवैध आहे.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'काहीतरी चुकले.',
];
