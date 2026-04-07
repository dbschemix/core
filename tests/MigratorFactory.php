<?php

declare(strict_types=1);

namespace dbschemix\core\tests;

use dbschemix\core\connection\DriverInterface;
use dbschemix\core\event\EventSubscriberInterface;
use dbschemix\core\Migration;
use dbschemix\core\Migrator;
use dbschemix\core\MigratorInterface;

final readonly class MigratorFactory
{
    private function __construct()
    {
    }

    public static function makeFromDriver(DriverInterface $driver): MigratorInterface
    {
        return new Migrator(
            list: [
                new Migration(
                    path: __DIR__ . '/migration/sqlite/memory',
                    driver: $driver,
                ),
            ],
        );
    }

    /**
     * @param list<EventSubscriberInterface> $eventSubscribers
     * @param non-empty-string $path
     */
    public static function makeFromEvent(
        DriverInterface $driver,
        array $eventSubscribers = [],
        string $path = __DIR__ . '/migration/test/event',
    ): MigratorInterface {
        return new Migrator(
            list: [
                new Migration(
                    path: $path,
                    driver: $driver,
                ),
            ],
            eventSubscribers: $eventSubscribers,
        );
    }
}
