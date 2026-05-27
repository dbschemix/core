<?php

declare(strict_types=1);

namespace dbschemix\core\event;

use Closure;

/**
 * @api
 */
final readonly class Subscription
{
    /**
     * @param Closure(Event $name, EventInterface $event):void $callback
     */
    public function __construct(
        public Event $event,
        public Closure $callback,
    ) {
    }
}
