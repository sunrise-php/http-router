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

namespace Sunrise\Http\Router\OpenApi;

use RuntimeException;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
interface OpenApiDocumentManagerInterface
{
    /**
     * @param array<array-key, RouteInterface> $routes
     *
     * @return array<array-key, mixed>
     */
    public function buildDocument(array $routes): array;

    /**
     * @param array<array-key, mixed> $document
     *
     * @throws RuntimeException
     */
    public function saveDocument(array $document): void;

    /**
     * @return resource
     *
     * @throws RuntimeException
     */
    public function openDocument();
}
