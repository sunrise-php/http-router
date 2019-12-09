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
 * OAS OAuth Flow Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oauth-flow-object
 */
class OAuthFlow extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowauthorizationurl
     */
    protected $authorizationUrl;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowtokenurl
     */
    protected $tokenUrl;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowrefreshurl
     */
    protected $refreshUrl;

    /**
     * @var string[]
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowscopes
     */
    protected $scopes;

    /**
     * @param string $authorizationUrl
     *
     * @return void
     */
    public function setAuthorizationUrl(string $authorizationUrl) : void
    {
        $this->authorizationUrl = $authorizationUrl;
    }

    /**
     * @param string $tokenUrl
     *
     * @return void
     */
    public function setTokenUrl(string $tokenUrl) : void
    {
        $this->tokenUrl = $tokenUrl;
    }

    /**
     * @param string $refreshUrl
     *
     * @return void
     */
    public function setRefreshUrl(string $refreshUrl) : void
    {
        $this->refreshUrl = $refreshUrl;
    }

    /**
     * @param string $name
     * @param string $description
     *
     * @return void
     */
    public function addScope(string $name, string $description) : void
    {
        $this->scopes[$name] = $description;
    }
}
