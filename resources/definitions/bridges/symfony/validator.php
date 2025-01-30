<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

return [
    ValidatorInterface::class => static fn(ContainerInterface $container): ValidatorInterface => (new ValidatorBuilder())
        ->enableAttributeMapping()
        ->setConstraintValidatorFactory(new ContainerConstraintValidatorFactory($container))
        ->getValidator(),
];
