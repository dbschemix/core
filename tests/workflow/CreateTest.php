<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestStorage;
use Override;
use PHPUnit\Framework\TestCase;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\tests\MigratorFactory;
use dbschemix\core\Config;
use dbschemix\core\InputOptions;
use Throwable;

final class CreateTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testCreate(): void
    {
        $driver = new TestDriver(new TestStorage());

        $path = dirname(__DIR__) . '/migration/test';
        $migrator = MigratorFactory::makeFromEvent(driver: $driver, path: $path);
        $command = $driver->makeCommand(new Config(table: 'migration'));

        $migrator->init();

        $migrator->up();
        $countMigration = count($command->fetchApplied());

        $migrator->create(new InputOptions(dbName: 'test/storage', migrationName: 'test'));
        $migrator->create(new InputOptions(dbName: 'test/storage', migrationName: '2test'));

        $migrator->up();
        self::assertCount($countMigration + 2, $command->fetchApplied());
    }

    /**
     * @throws Throwable
     */
    public function testCreateException(): void
    {
        $driver = new TestDriver(new TestStorage());

        $path = dirname(__DIR__) . '/migration/unknown';
        $migrator = MigratorFactory::makeFromEvent(driver: $driver, path: $path);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/^the dir .+ is not writable or does not exist.$/i');

        $migrator->create(new InputOptions(dbName: 'test/storage', migrationName: 'test'));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $pattern = dirname(__DIR__) . '/migration/test/*test.sql';
        foreach (glob($pattern) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
