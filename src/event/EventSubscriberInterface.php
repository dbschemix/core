<?php

declare(strict_types=1);

namespace dbschemix\core\event;

interface EventSubscriberInterface
{
    /**
     * @return array<string, callable(Event $name, EventInterface $event):void>
     */
    public function subscriptions(): array;
}
