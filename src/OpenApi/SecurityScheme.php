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
 * OAS Security Scheme Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#security-scheme-object
 */
class SecurityScheme extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemetype
     */
    protected $type;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemedescription
     */
    protected $description;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemename
     */
    protected $name;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemein
     */
    protected $in;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemescheme
     */
    protected $scheme;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemebearerformat
     */
    protected $bearerFormat;

    /**
     * @var OAuthFlows
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemeflows
     */
    protected $flows;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-securityschemeopenidconnecturl
     */
    protected $openIdConnectUrl;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
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
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @param string $in
     *
     * @return void
     */
    public function setIn(string $in) : void
    {
        $this->in = $in;
    }

    /**
     * @param string $scheme
     *
     * @return void
     */
    public function setScheme(string $scheme) : void
    {
        $this->scheme = $scheme;
    }

    /**
     * @param string $bearerFormat
     *
     * @return void
     */
    public function setBearerFormat(string $bearerFormat) : void
    {
        $this->bearerFormat = $bearerFormat;
    }

    /**
     * @param OAuthFlows $flows
     *
     * @return void
     */
    public function setFlows(OAuthFlows $flows) : void
    {
        $this->flows = $flows;
    }

    /**
     * @param string $openIdConnectUrl
     *
     * @return void
     */
    public function setOpenIdConnectUrl(string $openIdConnectUrl) : void
    {
        $this->openIdConnectUrl = $openIdConnectUrl;
    }
}
