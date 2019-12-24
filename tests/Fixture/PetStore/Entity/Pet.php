<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\PetStore\Entity;

/**
 * @OpenApi\Schema(
 *   refName = "Pet",
 *   type = "object",
 *   required = {
 *     "id",
 *     "name",
 *   },
 *   properties={
 *     "id" = @OpenApi\Schema(
 *       type = "integer",
 *       format = "int64",
 *     ),
 *     "name" = @OpenApi\Schema(
 *       type = "string",
 *     ),
 *     "tag" = @OpenApi\Schema(
 *       type = "string",
 *     ),
 *   },
 * )
 */
class Pet
{
}
