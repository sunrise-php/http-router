<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use Sunrise\Http\Router\Exception\CodecException;

/**
 * @since 3.0.0
 */
interface CodecManagerInterface
{
    public function supportsMediaType(MediaTypeInterface ...$mediaTypes): bool;

    /**
     * @throws CodecException
     */
    public function encode(MediaTypeInterface $mediaType, mixed $data, array $context): string;

    /**
     * @throws CodecException
     */
    public function decode(MediaTypeInterface $mediaType, string $data, array $context): mixed;
}
