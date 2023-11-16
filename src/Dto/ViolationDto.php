<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Dto;

use Sunrise\Hydrator\Exception\InvalidValueException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @since 3.0.0
 */
final class ViolationDto
{

    /**
     * Constructor of the class
     *
     * @param string $source
     * @param string $message
     */
    public function __construct(
        public string $source,
        public string $message,
    ) {
    }

    /**
     * Creates the violation from the given hydrator violation
     *
     * @param InvalidValueException $violation
     *
     * @return self
     */
    public static function fromHydratorViolation(InvalidValueException $violation): self
    {
        return new self(
            $violation->getPropertyPath(),
            $violation->getMessage(),
        );
    }

    /**
     * Creates the violation from the given validator violation
     *
     * @param ConstraintViolationInterface $violation
     *
     * @return self
     */
    public static function fromValidatorViolation(ConstraintViolationInterface $violation): self
    {
        return new self(
            $violation->getPropertyPath(),
            (string) $violation->getMessage(),
        );
    }
}
