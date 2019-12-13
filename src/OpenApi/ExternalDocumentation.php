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
 * OAS External Documentation Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#external-documentation-object
 */
class ExternalDocumentation extends AbstractObject
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-externaldocurl
     */
    protected $url;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-externaldocdescription
     */
    protected $description;

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
}
