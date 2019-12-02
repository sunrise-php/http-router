<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\OpenApi\Annotation\OpenApi;

/**
 * @Annotation
 *
 * @Target({"ANNOTATION"})
 *
 * @link https://swagger.io/docs/specification/2-0/describing-responses/
 */
final class Response
{

    /**
     * @Required
     *
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $schema;
}
