<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatolii Nekhai
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Annotation;

use Attribute;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Pay attention to the {@see StatusCodeInterface} dictionary.
 *
 * @link https://dev.sunrise-studio.io/docs/reference/routing-annotations?id=responsestatus
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ResponseStatus implements StatusCodeInterface
{
    public function __construct(
        public readonly int $code,
        public readonly string $phrase = '',
    ) {
    }
}
