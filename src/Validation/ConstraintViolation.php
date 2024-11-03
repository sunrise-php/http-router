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

namespace Sunrise\Http\Router\Validation;

/**
 * @since 3.0.0
 */
final class ConstraintViolation implements ConstraintViolationInterface
{
    public function __construct(
        private readonly string $message,
        private readonly string $messageTemplate,
        /** @var array<string, mixed> */
        private readonly array $messagePlaceholders,
        private readonly string $propertyPath,
        private readonly ?string $code,
        private readonly mixed $invalidValue,
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

    /**
     * @inheritDoc
     */
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

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }
}
