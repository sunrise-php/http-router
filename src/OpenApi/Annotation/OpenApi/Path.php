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
 * @Target({"CLASS"})
 *
 * @link https://swagger.io/docs/specification/2-0/paths-and-operations/
 */
final class Path
{

    /**
     * @Required
     *
     * @var array<string>
     */
    public $tags;

    /**
     * @Required
     *
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var array<string>
     */
    public $consumes = [];

    /**
     * @var array<string>
     */
    public $produces = [];

    /**
     * @var array<Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Parameter>
     */
    public $parameters = [];

    /**
     * @var array<Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Response>
     */
    public $responses = [];

    /**
     * @var bool
     */
    public $deprecated = false;
}
