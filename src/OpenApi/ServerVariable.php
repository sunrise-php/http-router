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
 * OAS Server Variable Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#server-variable-object
 */
class ServerVariable extends AbstractObject
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-servervariableenum
     */
    protected $enum;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-servervariabledefault
     */
    protected $default;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-servervariabledescription
     */
    protected $description;

    /**
     * @param string $name
     * @param string $default
     */
    public function __construct(string $name, string $default)
    {
        $this->name = $name;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string ...$enum
     *
     * @return void
     */
    public function setEnum(string ...$enum) : void
    {
        $this->enum = $enum;
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
}
