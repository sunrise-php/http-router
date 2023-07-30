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

namespace Sunrise\Http\Router\Annotation;

use Attribute;
use Fig\Http\Message\StatusCodeInterface;

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ResponseBody
{
    public const FORMAT_CSV = 'csv';
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';
    public const FORMAT_YAML = 'yaml';

    /**
     * Constructor of the class
     *
     * @param int<100, 599> $statusCode
     * @param array<non-empty-string, string|list<string>> $headers
     * @param non-empty-string $format
     */
    public function __construct(
        public int $statusCode = StatusCodeInterface::STATUS_OK,
        public array $headers = [],
        public string $format = self::FORMAT_JSON,
    ) {
    }
}
