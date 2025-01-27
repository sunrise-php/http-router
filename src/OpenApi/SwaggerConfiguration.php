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

/**
 * @since 3.0.0
 */
final class SwaggerConfiguration
{
    public const DEFAULT_TEMPLATE_FILENAME = __DIR__ . '/../../resources/templates/swagger.phtml';

    public const DEFAULT_CSS_URLS = [
        'https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui.min.css',
    ];

    public const DEFAULT_JS_URLS = [
        'https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui-bundle.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.18.2/swagger-ui-standalone-preset.min.js',
    ];

    public const DEFAULT_AUTO_RENDER = true;

    public function __construct(
        public readonly string $templateFilename = self::DEFAULT_TEMPLATE_FILENAME,
        /** @var string[] */
        public readonly array $cssUrls = self::DEFAULT_CSS_URLS,
        /** @var string[] */
        public readonly array $jsUrls = self::DEFAULT_JS_URLS,
        public readonly bool $autoRender = self::DEFAULT_AUTO_RENDER,
    ) {
    }
}
