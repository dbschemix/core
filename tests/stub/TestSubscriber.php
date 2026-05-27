<?php

declare(strict_types=1);

namespace dbschemix\core\tests\stub;

use Override;
use dbschemix\core\event\Event;
use dbschemix\core\event\EventInterface;
use dbschemix\core\event\EventSubscriberInterface;
use dbschemix\core\event\Subscription;

final class TestSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<string, string>
     */
    private array $storage = [];

    #[Override]
    public function subscriptions(): array
    {
        $subscriptions = [];
        foreach (Event::cases() as $event) {
            $subscriptions[] = new Subscription($event, $this->set(...));
        }

        return $subscriptions;
    }

    public function set(Event $name, EventInterface $event): void
    {
        $this->storage[$name->value] = $event->getMessage();
    }

    public function get(Event $name): string
    {
        return $this->storage[$name->value] ?? '';
    }

    public function clear(): void
    {
        $this->storage = [];
    }
}
