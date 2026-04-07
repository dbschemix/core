<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use Override;
use Throwable;
use PHPUnit\Framework\TestCase;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestStorage;
use dbschemix\core\tests\MigratorFactory;
use dbschemix\core\Config;
use dbschemix\core\InputOptions;
use dbschemix\core\MigratorInterface;

/**
 * Запускаем мигратор с аргументами.
 */
final class ArgumentsTest extends TestCase
{
    private MigratorInterface $migrator;

    private CommandInterface $command;

    #[Override]
    protected function setUp(): void
    {
        $driver = new TestDriver(new TestStorage());

        $this->migrator = MigratorFactory::makeFromDriver($driver);
        $this->command = $driver->makeCommand(new Config(table: 'migration'));
    }

    /**
     * @throws Throwable
     */
    public function testUpWithLimit(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up(new InputOptions(limit: 1));
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['202501011024_entity_create.sql']);

        $this->migrator->up(new InputOptions(limit: 2));
        $data = $this->command->fetchApplied();
        self::assertCount(3, $data);

        // check order
        $names = array_keys($data);
        self::assertEquals('202501021025_account_email.sql', $names[0]);
        self::assertEquals('202501021024_account_create.sql', $names[1]);
        self::assertEquals('202501011024_entity_create.sql', $names[2]);
    }

    /**
     * @throws Throwable
     */
    public function testDownWithLimit(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up();

        $this->migrator->down(new InputOptions(limit: 1));
        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);

        $this->migrator->down(new InputOptions(limit: 2));
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);
    }

    /**
     * @throws Throwable
     */
    public function testWithDryRun(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up();
        $data = $this->command->fetchApplied();
        self::assertCount(3, $data);

        $this->migrator->down(new InputOptions(dryRun: true));
        $data = $this->command->fetchApplied();
        self::assertCount(3, $data);

        $this->migrator->down();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);
    }

    /**
     * @throws Throwable
     */
    public function testWithDb(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up(new InputOptions(limit: 2, dbName: 'test/storage'));
        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);
    }

    public function testWithUnknownDb(): void
    {
        $this->migrator->init();

        $this->expectException(ConfigurationException::class);
        $this->migrator->up(new InputOptions(dbName: 'sqlite/unknown'));
    }

    /**
     * @throws Throwable
     */
    public function testDownWithVersion(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up(new InputOptions(limit: 2));
        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);

        $version = (int)current($data);
        self::assertGreaterThan(0, $version);

        // not found version
        $this->migrator->down(new InputOptions(version: 2));
        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);

        // down with version
        $this->migrator->down(new InputOptions(version: $version));
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);
    }

    /**
     * @throws Throwable
     */
    public function testDownWithLatestVersion(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $args = new InputOptions(limit: 1);
        self::assertFalse($args->hasApplyLatestVersion());

        $this->migrator->up($args);
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);

        usleep(10_000);

        $this->migrator->up($args); // +1
        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);

        $this->migrator->up($args); // +1
        $data = $this->command->fetchApplied();
        self::assertCount(3, $data);

        $args = new InputOptions();
        self::assertFalse($args->hasApplyLatestVersion());

        $args = new InputOptions(version: 111, applyLatestVersion: true);
        self::assertFalse($args->hasApplyLatestVersion());

        $args = new InputOptions(limit: 1, applyLatestVersion: true);
        self::assertFalse($args->hasApplyLatestVersion());

        $args = new InputOptions(applyLatestVersion: true);
        self::assertTrue($args->hasApplyLatestVersion());

        // down with latest version
        $this->migrator->down($args);
        $data = $this->command->fetchApplied();
        self::assertNotEmpty($data);
    }
}
