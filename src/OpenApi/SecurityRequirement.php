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
 * OAS Security Requirement Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#security-requirement-object
 */
class SecurityRequirement implements ObjectInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $scopes = [];

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string ...$scopes
     *
     * @return void
     */
    public function setScopes(string ...$scopes) : void
    {
        $this->scopes = $scopes;
    }

    /**
     * {@inheritDoc}
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#security-requirement-object-examples
     */
    public function toArray() : array
    {
        return [$this->name => $this->scopes];
    }
}
