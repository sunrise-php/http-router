<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\PetStore;

/**
 * Endpoint
 */
abstract class Endpoint
{

    /**
     * @OpenApi\Parameter(
     *   refName = "queryLimit",
     *   name = "limit",
     *   in = "query",
     *   description = "How many items to return at one time (max 100)",
     *   required = false,
     *   schema = @OpenApi\Schema(
     *     type = "integer",
     *     format = "int32",
     *   ),
     * )
     *
     * @var int
     */
    protected $limit = 50;

    /**
     * @OpenApi\Schema(
     *   refName = "Error",
     *   type = "object",
     *   required = {
     *     "code",
     *     "message",
     *   },
     *   properties = {
     *     "code" = @OpenApi\Schema(
     *       type = "integer",
     *       format = "int32",
     *     ),
     *     "message" = @OpenApi\Schema(
     *       type = "string",
     *     ),
     *   },
     * )
     *
     * @param int $code
     * @param string $message
     *
     * @return array
     */
    protected function error(int $code, string $message) : array
    {
        return \compact('code', 'message');
    }
}
