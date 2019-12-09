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
 * OAS Info Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#info-object
 */
class Info extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infotitle
     */
    protected $title;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infodescription
     */
    protected $description;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infotermsofservice
     */
    protected $termsOfService;

    /**
     * @var Contact
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infocontact
     */
    protected $contact;

    /**
     * @var License
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infolicense
     */
    protected $license;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-infoversion
     */
    protected $version;

    /**
     * @param string $title
     * @param string $version
     */
    public function __construct(string $title, string $version)
    {
        $this->title = $title;
        $this->version = $version;
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
     * @param string $termsOfService
     *
     * @return void
     */
    public function setTermsOfService(string $termsOfService) : void
    {
        $this->termsOfService = $termsOfService;
    }

    /**
     * @param Contact $contact
     *
     * @return void
     */
    public function setContact(Contact $contact) : void
    {
        $this->contact = $contact;
    }

    /**
     * @param License $license
     *
     * @return void
     */
    public function setLicense(License $license) : void
    {
        $this->license = $license;
    }
}
