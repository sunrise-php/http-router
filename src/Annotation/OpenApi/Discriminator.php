<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation\OpenApi;

/**
 * @Annotation
 *
 * @Target({"ANNOTATION"})
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#discriminator-object
 */
final class Discriminator extends AbstractAnnotation implements DiscriminatorInterface
{

    /**
     * @Required
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-propertyname
     */
    public $propertyName;

    /**
     * @var array<string>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-discriminatormapping
     */
    public $mapping;
}
