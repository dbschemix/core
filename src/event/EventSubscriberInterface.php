<?php

declare(strict_types=1);

namespace dbschemix\core\event;

/**
 * @api
 */
interface EventSubscriberInterface
{
    /**
     * Returns the list of (event, callback) subscriptions this subscriber wants.
     *
     * Subscriber callbacks MUST NOT throw. If a callback throws, the library
     * reports the failure via trigger_error(E_USER_WARNING) and continues:
     * other subscribers still receive the event, and the migration is not
     * aborted. Subscribers that need to react to their own failures must
     * wrap their body in their own try/catch.
     *
     * @return list<Subscription>
     */
    public function subscriptions(): array;
}
