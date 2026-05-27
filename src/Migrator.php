<?php

declare(strict_types=1);

namespace dbschemix\core;

use Override;
use dbschemix\core\event\ExceptionEvent;
use dbschemix\core\event\Event;
use dbschemix\core\event\EventDispatcher;
use dbschemix\core\event\EventSubscriberInterface;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\internal\action\Workflow;

/**
 * @api
 */
final readonly class Migrator implements MigratorInterface
{
    private Workflow $actionWorkflow;

    private EventDispatcher $eventDispatcher;

    /**
     * @param non-empty-list<Migration> $list
     * @param list<EventSubscriberInterface> $eventSubscribers
     */
    public function __construct(
        private array $list,
        array $eventSubscribers = [],
    ) {
        $this->eventDispatcher = new EventDispatcher($eventSubscribers);
        $this->actionWorkflow = new Workflow($this->eventDispatcher);
    }

    #[Override]
    public function init(): void
    {
        foreach ($this->list as $migration) {
            $this->actionWorkflow->initialization($migration);
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions and withers operate on validated state.
     */
    #[Override]
    public function create(InputOptions $args = new InputOptions()): void
    {
        if ($args->dbName === null) {
            throw new ConfigurationException(
                "DBName must be declared."
            );
        }

        if ($args->migrationName === null) {
            throw new ConfigurationException(
                "Migration Name must be declared."
            );
        }

        foreach ($this->selectDb($args) as $migration) {
            $this->actionWorkflow->create($migration, $args->migrationName);
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions operates on validated state.
     */
    #[Override]
    public function up(InputOptions $args = new InputOptions()): void
    {
        foreach ($this->selectDb($args) as $migration) {
            $this->actionWorkflow->up($migration, $args);
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions operates on validated state.
     */
    #[Override]
    public function down(InputOptions $args = new InputOptions()): void
    {
        foreach ($this->selectDb($args) as $migration) {
            $this->actionWorkflow->down($migration, $args);
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions operates on validated state.
     */
    #[Override]
    public function fixture(InputOptions $args = new InputOptions()): void
    {
        foreach ($this->selectDb($args) as $migration) {
            $this->actionWorkflow->fixture($migration, $args);
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions and withers operate on validated state.
     */
    #[Override]
    public function redo(InputOptions $args = new InputOptions()): void
    {
        $this->down($args);
        $this->up($args->withResetLimit());
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Default InputOptions and withers operate on validated state.
     */
    #[Override]
    public function verify(InputOptions $args = new InputOptions()): void
    {
        foreach ($this->selectDb($args) as $migration) {
            $version = $this->actionWorkflow->up(
                $migration,
                $args->withExactlyAll(),
            );

            if ($args->dryRun === false) {
                $this->actionWorkflow->down(
                    $migration,
                    $args->withVersion($version),
                );
            }
        }
    }

    /**
     * @return iterable<Migration>
     * @throws ConfigurationException
     */
    private function selectDb(InputOptions $args): iterable
    {
        $selectDatabase = $args->dbName;
        if ($selectDatabase === null) {
            return $this->list;
        }

        $list = array_filter(
            $this->list,
            static fn(Migration $migration): bool => $migration->getName() === $selectDatabase,
        );

        if ($list === []) {
            $exception = new ConfigurationException(
                "[$selectDatabase] no such database in the configuration."
            );

            $this->eventDispatcher->trigger(
                Event::ConfigurationError,
                new ExceptionEvent($selectDatabase, $exception)
            );

            throw $exception;
        }

        return $list;
    }
}
