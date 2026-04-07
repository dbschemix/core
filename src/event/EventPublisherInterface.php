<?php

declare(strict_types=1);

namespace dbschemix\core\event;

interface EventPublisherInterface
{
    public function on(EventSubscriberInterface $subscriber): void;

    public function off(EventSubscriberInterface $subscriber): void;
}
