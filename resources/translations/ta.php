<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'கோரிக்கை URI தவறானது மற்றும் சர்வரால் ஏற்றுக்கொள்ள முடியாது.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'இந்த URIக்கு கோரிக்கப்பட்ட வளம் கிடைக்கவில்லை.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'இந்த வளத்திற்கு கோரிக்கப்பட்ட முறை அனுமதிக்கப்படவில்லை.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'கோரிக்கை ஊடக வகை இல்லை.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'இந்த வளம் கோரிக்கை ஊடக வகையை ஆதரிக்கவில்லை.',
    ErrorMessage::INVALID_VARIABLE => 'கோரிக்கை URI "{{ route_uri }}"ல் {{{ variable_name }}} மாறியின் மதிப்பு தவறானது.',
    ErrorMessage::INVALID_QUERY => 'கோரிக்கை விசாரித்தல் அளவுருக்கள் தவறானவை.',
    ErrorMessage::MISSING_HEADER => 'கோரிக்கை தலைப்பு "{{ header_name }}" இல்லை.',
    ErrorMessage::INVALID_HEADER => 'கோரிக்கை தலைப்பு "{{ header_name }}" தவறானது.',
    ErrorMessage::MISSING_COOKIE => 'குக்கீ "{{ cookie_name }}" இல்லை.',
    ErrorMessage::INVALID_COOKIE => 'குக்கீ "{{ cookie_name }}" தவறானது.',
    ErrorMessage::INVALID_BODY => 'கோரிக்கை உடல் தவறானது.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'ஏதோ தவறாகிவிட்டது.',
];
