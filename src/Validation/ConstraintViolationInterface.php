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

    /**
     * Gets the violation's interpolated message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Gets the violation's message template
     *
     * @return string
     */
    public function getMessageTemplate(): string;

    /**
     * Gets the violation's message placeholders
     *
     * @return array<string, mixed>
     */
    public function getMessagePlaceholders(): array;

    /**
     * Gets the violation's property path
     *
     * @return string
     */
    public function getPropertyPath(): string;
}
