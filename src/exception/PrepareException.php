<?php

declare(strict_types=1);

namespace dbschemix\core\exception;

/**
 * Thrown when a database driver cannot prepare a SQL statement for execution.
 *
 * Intended as an extension point for driver implementations in downstream
 * packages (dbschemix/pdo, dbschemix/pgsql, dbschemix/clickhouse, etc.). The
 * core workflow does not throw this directly; downstream drivers throw it
 * from within ConnectionInterface / StatementInterface implementations and
 * Workflow::run() catches it through the generic Throwable handler, wrapping
 * it into ActionException with the original PrepareException as the cause.
 *
 * @api
 */
final class PrepareException extends MigratorException
{
}
