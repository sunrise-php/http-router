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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use JsonSerializable;

/**
 * Errors DTO
 *
 * @OA\SchemaObject({
 *   "errors": @OA\SchemaReference(".errors"),
 * })
 *
 * @OA\Response(
 *   description="Something went wrong",
 *   content={
 *     "application/json": @OA\MediaType(
 *       schema=@OA\SchemaReference("ErrorsDto"),
 *     ),
 *   },
 * )
 *
 * @since 3.0.0
 */
final class ErrorsDto implements JsonSerializable
{

    /**
     * @OA\SchemaArray(
     *   @OA\SchemaReference("ErrorDto")
     * )
     *
     * @var list<ErrorDto>
     */
    private array $errors = [];

    /**
     * @param ErrorDto ...$errors
     */
    public function __construct(ErrorDto ...$errors)
    {
        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
    }

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @return self
     */
    public static function fromViolations(ConstraintViolationListInterface $violations): self
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = ErrorDto::fromViolation($violation);
        }

        return new self(...$errors);
    }

    /**
     * @return array{errors: list<ErrorDto>}
     */
    public function jsonSerialize(): array
    {
        return [
            'errors' => $this->errors,
        ];
    }
}
