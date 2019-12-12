<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\OpenApi;

/**
 * OAS OpenAPI Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#openapi-object
 */
class OpenApi extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oasversion
     */
    protected $openapi;

    /**
     * @var Info
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oasinfo
     */
    protected $info;

    /**
     * @var Server[]
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oasservers
     */
    protected $servers = [];

    /**
     * @var array
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oaspaths
     */
    protected $paths = [];

    /**
     * @var array
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oascomponents
     */
    protected $components = [];

    /**
     * @var SecurityRequirement[]
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oassecurity
     */
    protected $security = [];

    /**
     * @var array
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oastags
     *
     * @todo need to implement the missing objects...
     */
    protected $tags;

    /**
     * @var array
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oasexternaldocs
     *
     * @todo need to implement the missing objects...
     */
    protected $externalDocs;

    /**
     * @param string $openapi
     * @param Info $info
     */
    public function __construct(string $openapi, Info $info)
    {
        $this->openapi = $openapi;
        $this->info = $info;
    }

    /**
     * @param Server ...$servers
     *
     * @return void
     */
    public function addServer(Server ...$servers) : void
    {
        foreach ($servers as $server) {
            $this->servers[] = $server;
        }
    }

    /**
     * @param ComponentObjectInterface ...$objects
     *
     * @return void
     */
    public function addComponentObject(ComponentObjectInterface ...$objects) : void
    {
        foreach ($objects as $object) {
            $this->components[$object->getComponentName()][$object->getReferenceName()] = $object;
        }
    }

    /**
     * @param SecurityRequirement ...$requirements
     *
     * @return void
     */
    public function addSecurityRequirement(SecurityRequirement ...$requirements) : void
    {
        foreach ($requirements as $requirement) {
            $this->security[] = $requirement;
        }
    }
}
