<?php

declare(strict_types=1);

namespace dbschemix\core\command;

use Throwable;
use dbschemix\core\Context;

/**
 * Command port: the contract a database driver implementation fulfills to
 * run core migration workflow operations.
 *
 * Audience: driver implementors (the in-tree adapter
 * `internal\command\Command` plus downstream packages such as
 * `dbschemix/pdo`, `dbschemix/pgsql`, `dbschemix/clickhouse`). End consumers
 * interact with `Migrator`, whose exception contract is fully typed.
 *
 * Exception policy: each method below is declared `@throws Throwable`
 * intentionally. A driver implementation may surface whatever its
 * underlying client throws (\PDOException, mysqli_sql_exception, ClickHouse
 * client exceptions, ...) plus library-typed exceptions such as
 * `PrepareException`. The core workflow catches these at its boundary
 * (Workflow::run, Workflow::getAppliedMigrations) and wraps them into
 * typed `MigratorException` subtypes — `ActionException` for migration
 * failures, `InitializationException` for system-state reads — so the
 * typed exception contract is restored before reaching the consumer of
 * `Migrator`. Driver implementors are NOT required to pre-wrap their
 * underlying exceptions; the workflow's `catch (Throwable)` is the
 * wrapping point by design.
 *
 * @api
 */
interface CommandInterface
{
    /**
     * @return array<non-empty-string, non-negative-int>
     * @throws Throwable
     */
    public function fetchApplied(Options $options = new Options()): array;

    /**
     * @return bool true: request completed; false: request rejected
     * @throws Throwable
     */
    public function up(Context $context): bool;

    /**
     * @return bool true: request completed; false: request rejected
     * @throws Throwable
     */
    public function down(Context $context): bool;

    /**
     * @return bool true: request completed; false: request rejected
     * @throws Throwable
     */
    public function exec(Context $context): bool;
}
