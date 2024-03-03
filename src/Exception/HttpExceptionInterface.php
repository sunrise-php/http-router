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

use Sunrise\Http\Router\ConstraintViolationInterface;

/**
 * The package's base HTTP exception interface
 *
 * @since 3.0.0
 */
interface HttpExceptionInterface extends ExceptionInterface
{
    public function getMessageTemplate(): string;

    public function getMessagePlaceholders(): array;

    public function getStatusCode(): int;

    /**
     * @return list<array{0: TFieldName, 1: TFieldValue}>
     *
     * @template TFieldName as string
     * @template TFieldValue as string
     */
    public function getHeaderFields(): array;

    /**
     * @return list<ConstraintViolationInterface>
     */
    public function getConstraintViolations(): array;
}
