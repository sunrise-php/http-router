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
 * OAS Server Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#server-object
 */
class Server extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-serverurl
     */
    protected $url;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-serverdescription
     */
    protected $description;

    /**
     * @var ServerVariable[]
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-servervariables
     */
    protected $variables = [];

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    /**
     * @param ServerVariable ...$variables
     *
     * @return void
     */
    public function addVariable(ServerVariable ...$variables) : void
    {
        foreach ($variables as $variable) {
            $this->variables[$variable->getName()] = $variable;
        }
    }
}
