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
interface ConstraintViolationInterface
{
    public function getMessage(): string;

    public function getMessageTemplate(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function getMessagePlaceholders(): array;

    public function getPropertyPath(): string;

    public function getCode(): ?string;

    public function getInvalidValue(): mixed;
}
