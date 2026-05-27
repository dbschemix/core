<?php

declare(strict_types=1);

namespace dbschemix\core\event;

use Throwable;

/**
 * @psalm-internal dbschemix\core
 */
final readonly class EventDispatcher
{
    /**
     * @var array<string, list<callable(Event $name, EventInterface $event):void>>
     */
    private array $eventHandlers;

    /**
     * @param list<EventSubscriberInterface> $eventSubscribers
     */
    public function __construct(array $eventSubscribers)
    {
        $subscriptions = [];
        foreach ($eventSubscribers as $subscriber) {
            foreach ($subscriber->subscriptions() as $name => $callback) {
                $subscriptions[$name][] = $callback;
            }
        }

        $this->eventHandlers = $subscriptions;
    }

    /**
     * Notifies subscribers of an event.
     *
     * Subscriber callbacks MUST NOT throw. If one does, the exception is captured
     * and reported through trigger_error(E_USER_WARNING); the loop continues so
     * remaining subscribers still receive the event, and the migration is not
     * aborted by a subscriber bug.
     *
     * This isolation is intentional: trigger() is called from inside the migration's
     * own try/catch flow (see Workflow::run()), and an exception propagating out
     * of a subscriber would be misattributed as a migration failure — corrupting the
     * success/error event contract and potentially shadowing the original migration
     * error.
     *
     * Hosts that want fail-fast on subscriber failures may install a strict error
     * handler (set_error_handler) that converts E_USER_WARNING into an exception;
     * that is an explicit opt-in by the host.
     */
    public function trigger(Event $name, EventInterface $event): void
    {
        if (array_key_exists($name->value, $this->eventHandlers)) {
            foreach ($this->eventHandlers[$name->value] as $subscriberCallback) {
                try {
                    $subscriberCallback($name, $event);
                } catch (Throwable $exception) {
                    trigger_error(
                        sprintf(
                            'dbschemix\core: subscriber callback for event "%s" failed (%s): %s',
                            $name->value,
                            $exception::class,
                            $exception->getMessage(),
                        ),
                        E_USER_WARNING,
                    );
                }
            }
        }
    }
}
