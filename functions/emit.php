<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;

/**
 * Import functions
 */
use function header;
use function sprintf;

/**
 * Sends the given response
 *
 * Don't use the function in your production environment, it's only for tests!
 *
 * @param ResponseInterface $response
 *
 * @return void
 */
function emit(ResponseInterface $response) : void
{
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf(
                '%s: %s',
                $name,
                $value
            ), false);
        }
    }

    header(sprintf(
        'HTTP/%s %d %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    ), true);

    echo $response->getBody();
}
