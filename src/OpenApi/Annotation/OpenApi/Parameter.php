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
 * @link https://swagger.io/docs/specification/2-0/describing-parameters/
 */
final class Parameter
{

    /**
     * @Enum({
     *   "header",
     *   "query",
     *   "body",
     *   "formData"
     * })
     *
     * @Required
     *
     * @var string
     */
    public $in;

    /**
     * @Required
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var string
     */
    public $schema;
}
