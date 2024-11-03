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

namespace Sunrise\Http\Router\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;

/**
 * @since 3.0.0
 */
interface HttpExceptionInterface extends ExceptionInterface, StatusCodeInterface
{
    /**
     * Returns the exception's non-interpolated message.
     */
    public function getMessageTemplate(): string;

    /**
     * @return array<string, mixed>
     */
    public function getMessagePlaceholders(): array;

    /**
     * @return array<array-key, array{0: string, 1: string}>
     */
    public function getHeaderFields(): array;

    /**
     * @return array<array-key, ConstraintViolationInterface>
     */
    public function getConstraintViolations(): array;
}
