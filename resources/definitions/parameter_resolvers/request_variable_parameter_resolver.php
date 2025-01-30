<?php

declare(strict_types=1);

use Sunrise\Http\Router\ParameterResolver\RequestVariableParameterResolver;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'router.request_variable_parameter_resolver.default_error_status_code' => null,
    'router.request_variable_parameter_resolver.default_error_message' => null,
    'router.request_variable_parameter_resolver.hydrator_context' => [],
    'router.request_variable_parameter_resolver.default_validation_enabled' => true,

    'router.parameter_resolvers' => add([
        create(RequestVariableParameterResolver::class)
            ->constructor(
                hydrator: get(HydratorInterface::class),
                validator: get(ValidatorInterface::class),
                defaultErrorStatusCode: get('router.request_variable_parameter_resolver.default_error_status_code'),
                defaultErrorMessage: get('router.request_variable_parameter_resolver.default_error_message'),
                hydratorContext: get('router.request_variable_parameter_resolver.hydrator_context'),
                defaultValidationEnabled: get('router.request_variable_parameter_resolver.default_validation_enabled'),
            ),
    ]),
];
