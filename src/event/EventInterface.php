<?php

declare(strict_types=1);

namespace dbschemix\core\event;

/**
 * Common surface across all dispatched events.
 *
 * Provides generic-logging-friendly accessors (getName, getMessage) suitable
 * for subscribers that only need a human-readable line. Structured payload
 * data (Context, Throwable, dbName, action) lives on the concrete event
 * class — {@see MigrateSuccessEvent}, {@see MigrateErrorEvent},
 * {@see ExceptionEvent} — as public readonly properties.
 *
 * Subscribers that need structured data should type-narrow via `instanceof`
 * onto the concrete class. The Event enum case → concrete event class
 * mapping is part of the @api contract and is documented per case in
 * {@see Event}.
 *
 * @api
 */
interface EventInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * @return non-empty-string
     */
    public function getMessage(): string;
}
