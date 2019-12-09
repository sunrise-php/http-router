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
 * OAS OAuth Flows Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oauth-flows-object
 */
class OAuthFlows extends AbstractObject
{

    /**
     * @var OAuthFlow
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowsimplicit
     */
    protected $implicit;

    /**
     * @var OAuthFlow
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowspassword
     */
    protected $password;

    /**
     * @var OAuthFlow
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowsclientcredentials
     */
    protected $clientCredentials;

    /**
     * @var OAuthFlow
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-oauthflowsauthorizationcode
     */
    protected $authorizationCode;

    /**
     * @param OAuthFlow $implicit
     *
     * @return void
     */
    public function setImplicit(OAuthFlow $implicit) : void
    {
        $this->implicit = $implicit;
    }

    /**
     * @param OAuthFlow $password
     *
     * @return void
     */
    public function setPassword(OAuthFlow $password) : void
    {
        $this->password = $password;
    }

    /**
     * @param OAuthFlow $clientCredentials
     *
     * @return void
     */
    public function setClientCredentials(OAuthFlow $clientCredentials) : void
    {
        $this->clientCredentials = $clientCredentials;
    }

    /**
     * @param OAuthFlow $authorizationCode
     *
     * @return void
     */
    public function setAuthorizationCode(OAuthFlow $authorizationCode) : void
    {
        $this->authorizationCode = $authorizationCode;
    }
}
