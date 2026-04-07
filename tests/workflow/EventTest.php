<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use Throwable;
use PHPUnit\Framework\TestCase;
use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestDriverError;
use dbschemix\core\tests\stub\TestStorage;
use dbschemix\core\tests\stub\TestSubscriber;
use dbschemix\core\tests\MigratorFactory;
use dbschemix\core\event\Event;
use dbschemix\core\InputOptions;

final class EventTest extends TestCase
{
    public function testSelectDatabaseError(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        try {
            $migrator->init();
            $migrator->up(new InputOptions(dbName: 'test'));
        } catch (Throwable) {
        }

        self::assertEquals(
            '[test] no such database in the configuration.',
            $eventSubscriber->get(Event::ConfigurationError)
        );
    }

    public function testConnectionError(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriverError(),
            [
                $eventSubscriber,
            ]
        );

        try {
            $migrator->init();
        } catch (Throwable) {
        }

        self::assertStringContainsString(
            'Connection error',
            $eventSubscriber->get(Event::ConnectionError)
        );
    }

    public function testInitializationError(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        try {
            $migrator->up();
        } catch (Throwable) {
        }

        self::assertStringContainsString(
            'Migrator not initialization.',
            $eventSubscriber->get(Event::InitializationError)
        );
    }

    public function testMigration(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        $migrator->init();

        try {
            $migrator->up();
        } catch (Throwable) {
        }

        $version = substr((string)time(), 0, -2);

        self::assertStringContainsString(
            '202501011024_entity_create.sql, vers: ' . $version,
            $eventSubscriber->get(Event::MigrateSuccess)
        );

        self::assertStringContainsString(
            '202501021025_account_error.sql, vers: ' . $version,
            $eventSubscriber->get(Event::MigrateError)
        );
    }

    public function testMigrationDryRun(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        $migrator->init();

        try {
            $migrator->up(new InputOptions(dryRun: true));
        } catch (Throwable) {
        }

        self::assertStringContainsString(
            '202501021025_account_error.sql',
            $eventSubscriber->get(Event::MigrateSuccess)
        );

        // event-repeatable: does not exist, but does not start in dry-run mode
        self::assertStringNotContainsString(
            'does not exist.',
            $eventSubscriber->get(Event::FilesystemNotice)
        );
    }

    public function testMigrationFilesystemNotice(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        $migrator->init();
        $migrator->up(new InputOptions(limit: 1, hasRepeatable: true));

        // event-repeatable: does not exist
        self::assertStringContainsString(
            'does not exist.',
            $eventSubscriber->get(Event::FilesystemNotice)
        );
    }

    public function testMigrationUpDoesNotContainFiles(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ],
            dirname(__DIR__) . '/migration/sqlite/memory'
        );

        $migrator->init();
        $migrator->up();

        // migration completed in the previous step
        $migrator->up();
        self::assertStringContainsString(
            'does not contain migration files',
            $eventSubscriber->get(Event::FilesystemNotice)
        );
    }

    public function testMigrationDownDoesNotContainFiles(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ],
            dirname(__DIR__) . '/migration/sqlite/memory'
        );

        $migrator->init();
        $migrator->down();

        // migration completed in the previous step
        $migrator->down();
        self::assertStringContainsString(
            'does not contain migration files',
            $eventSubscriber->get(Event::FilesystemNotice)
        );
    }

    public function testFixtureDirectoryDoesNotExistError(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ]
        );

        $migrator->init();

        try {
            $migrator->fixture();
        } catch (Throwable) {
        }

        self::assertStringContainsString(
            'does not exist',
            $eventSubscriber->get(Event::FilesystemError)
        );
    }

    public function testCreateDirectoryDoesNotExistError(): void
    {
        $eventSubscriber = new TestSubscriber();
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage()),
            [
                $eventSubscriber,
            ],
            '/migration/unknown'
        );

        $migrator->init();

        try {
            $migrator->create(new InputOptions(dbName: 'test/storage', migrationName: 'test'));
        } catch (Throwable) {
        }

        self::assertStringContainsString(
            'is not writable or does not exist.',
            $eventSubscriber->get(Event::FilesystemError)
        );
    }
}
