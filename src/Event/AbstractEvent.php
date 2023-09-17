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

namespace Sunrise\Http\Router\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @internal
 *
 * @since 3.0.0
 */
abstract class AbstractEvent implements StoppableEventInterface
{

    /**
     * Indicates that the event's handling must be interrupted
     *
     * @var bool
     */
    private bool $isPropagationStopped = false;

    /**
     * @inheritDoc
     */
    final public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }

    /**
     * Interrupts the event's handling
     *
     * @return void
     */
    final public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }
}
