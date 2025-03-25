<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'അഭ്യർത്ഥന URI ശരിയായ രൂപത്തിൽ ഇല്ല, അതിനാൽ സെർവർ അതിനെ അംഗീകരിക്കില്ല.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'ഈ URI-ക്കായി അഭ്യർത്ഥിച്ച വിഭവം കണ്ടെത്തിയില്ല.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'ഈ വിഭവത്തിന് അഭ്യർത്ഥിച്ച മാർഗ്ഗം അനുവദനീയമല്ല.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'അഭ്യർത്ഥന മീഡിയ തരത്തിൽ കുറവുണ്ട്.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'ഈ വിഭവം അഭ്യർത്ഥന മീഡിയ തരം പിന്തുണയ്‌ക്കുന്നില്ല.',
    ErrorMessage::INVALID_VARIABLE => 'അഭ്യർത്ഥന URI "{{ route_uri }}" ലെ {{{ variable_name }}} വേരിയബിളിന്റെ മൂല്യം അസാധുവാണ്.',
    ErrorMessage::INVALID_QUERY => 'അഭ്യര്‍ത്ഥന ക്വറി പരാമീറ്ററുകള് അസാധുവാണ്.',
    ErrorMessage::MISSING_HEADER => 'അഭ്യർത്ഥന ഹെഡർ "{{ header_name }}" നഷ്ടപ്പെട്ടിരിക്കുന്നു.',
    ErrorMessage::INVALID_HEADER => 'അഭ്യർത്ഥന ഹെഡർ "{{ header_name }}" അസാധുവാണ്.',
    ErrorMessage::MISSING_COOKIE => 'കുക്കി "{{ cookie_name }}" നഷ്ടപ്പെട്ടിരിക്കുന്നു.',
    ErrorMessage::INVALID_COOKIE => 'കുക്കി "{{ cookie_name }}" അസാധുവാണ്.',
    ErrorMessage::INVALID_BODY => 'അഭ്യർത്ഥന ബോഡി അസാധുവാണ്.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'കുറച്ചുകാര്യങ്ങൾ പിഴച്ചു.',
];
