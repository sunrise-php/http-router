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

namespace Sunrise\Http\Router;

use Generator;
use Sunrise\Hydrator\Exception\InvalidValueException as HydratorConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolation;

/**
 * @since 3.0.0
 */
final class ConstraintViolation implements ConstraintViolationInterface
{
    public function __construct(
        private readonly string $message,
        private readonly string $messageTemplate,
        private readonly array $messagePlaceholders,
        private readonly string $propertyPath,
        private readonly ?string $code,
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return Generator<int, self>
     */
    public static function fromHydrator(HydratorConstraintViolation ...$violations): Generator
    {
        foreach ($violations as $violation) {
            yield new self(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getMessagePlaceholders(),
                $violation->getPropertyPath(),
                $violation->getErrorCode(),
            );
        }
    }

    /**
     * @return Generator<int, self>
     */
    public static function fromValidator(ValidatorConstraintViolation ...$violations): Generator
    {
        foreach ($violations as $violation) {
            yield new self(
                (string) $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getPropertyPath(),
                $violation->getCode(),
            );
        }
    }
}
