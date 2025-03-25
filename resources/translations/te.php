<?php

declare(strict_types=1);

use Sunrise\Http\Router\Dictionary\ErrorMessage;

return [
    ErrorMessage::MALFORMED_URI => 'అభ్యర్థన URI తప్పుగా ఉంది మరియు సర్వర్ ద్వారా ఆమోదించబడదు.',
    ErrorMessage::RESOURCE_NOT_FOUND => 'ఈ URI కి అభ్యర్థించిన వనరు కనుగొనబడలేదు.',
    ErrorMessage::METHOD_NOT_ALLOWED => 'ఈ వనరుకు అభ్యర్థించిన పద్ధతిని అనుమతించలేదు.',
    ErrorMessage::MISSING_MEDIA_TYPE => 'అభ్యర్థన మీడియా రకం లేదు.',
    ErrorMessage::UNSUPPORTED_MEDIA_TYPE => 'అభ్యర్థన మీడియా రకం ఈ వనరుతో ఆమోదించబడదు.',
    ErrorMessage::INVALID_VARIABLE => 'అభ్యర్థన URI "{{ route_uri }}" లో వేరియబుల్ {{{ variable_name }}} యొక్క విలువ తప్పుగా ఉంది.',
    ErrorMessage::INVALID_QUERY => 'అభ్యర్థన క్వెరీ పరామితులు తప్పుగా ఉన్నాయి.',
    ErrorMessage::MISSING_HEADER => 'అభ్యర్థన హెడ్డర్ "{{ header_name }}" గైర్హాజర్.',
    ErrorMessage::INVALID_HEADER => 'అభ్యర్థన హెడ్డర్ "{{ header_name }}" తప్పుగా ఉంది.',
    ErrorMessage::MISSING_COOKIE => 'కుక్కీ "{{ cookie_name }}" గైర్హాజర్.',
    ErrorMessage::INVALID_COOKIE => 'కుక్కీ "{{ cookie_name }}" తప్పుగా ఉంది.',
    ErrorMessage::INVALID_BODY => 'అభ్యర్థన బాడీ తప్పుగా ఉంది.',
    ErrorMessage::INTERNAL_SERVER_ERROR => 'ఏదో పొరపాటు జరిగింది.',
];
