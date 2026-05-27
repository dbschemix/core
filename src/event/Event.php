<?php

declare(strict_types=1);

namespace dbschemix\core\event;

/**
 * Library-wide event identifiers and the stable enum-to-payload mapping.
 *
 * Each case below documents the concrete {@see EventInterface} implementation
 * a subscriber will receive when the event fires. Subscribers that need the
 * structured payload (Context, Throwable, dbName, action) may safely
 * `instanceof` onto the dispatched class — the mapping is part of the @api
 * contract.
 *
 * @api
 */
enum Event: string
{
    /**
     * Reading the migration-tracking table failed during initialization.
     *
     * Dispatched payload: {@see ExceptionEvent} (dbName, exception).
     */
    case InitializationError = 'initialization-error-event';

    /**
     * The driver could not open a connection or build a CommandInterface.
     *
     * Dispatched payload: {@see ExceptionEvent} (dbName, exception).
     */
    case ConnectionError = 'connection-error-event';

    /**
     * Configuration is invalid (requested database is not in the list,
     * table name is malformed, required arguments missing, ...).
     *
     * Dispatched payload: {@see ExceptionEvent} (dbName, exception).
     */
    case ConfigurationError = 'configuration-error-event';

    /**
     * A filesystem operation failed (missing migration directory, unwritable
     * path, unreadable file, ...).
     *
     * Dispatched payload: {@see ExceptionEvent} (dbName, exception).
     */
    case FilesystemError = 'filesystem-error-event';

    /**
     * Filesystem soft event: directory exists but contains no migration files.
     * Informational; not an error. The carried exception holds the notice
     * message for logging convenience.
     *
     * Dispatched payload: {@see ExceptionEvent} (dbName, exception).
     */
    case FilesystemNotice = 'filesystem-notice-event';

    /**
     * A single migration file has been applied successfully.
     *
     * Dispatched payload: {@see MigrateSuccessEvent} (action, context).
     */
    case MigrateSuccess = 'migrate-success-event';

    /**
     * A single migration file failed to apply.
     *
     * Dispatched payload: {@see MigrateErrorEvent} (action, context, exception).
     */
    case MigrateError = 'migrate-error-event';
}
