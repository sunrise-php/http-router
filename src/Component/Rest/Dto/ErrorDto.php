<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Component\Rest\Dto;

/**
 * Import classes
 */
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi as OA;
use Symfony\Component\Validator\ConstraintViolationInterface;
use JsonSerializable;

/**
 * Error DTO
 *
 * @OA\SchemaObject({
 * })
 *
 * @link https://jsonapi.org/format/#error-objects
 *
 * @since 3.0.0
 */
final class ErrorDto implements JsonSerializable
{

    /**
     * @param ConstraintViolationInterface $violation
     *
     * @return self
     */
    public static function fromViolation(ConstraintViolationInterface $violation): self
    {
        return new ErrorDto();
    }

    /**
     * @return array{
     *         }
     */
    public function jsonSerialize(): array
    {
        return [
        ];
    }
}
