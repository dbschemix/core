<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use Override;
use Throwable;
use PHPUnit\Framework\TestCase;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\exception\ActionException;
use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestStorage;
use dbschemix\core\tests\MigratorFactory;
use dbschemix\core\Config;
use dbschemix\core\InputOptions;
use dbschemix\core\MigratorInterface;

final class ConsistencyTest extends TestCase
{
    private MigratorInterface $migrator;

    private CommandInterface $command;

    #[Override]
    protected function setUp(): void
    {
        $driver = new TestDriver(new TestStorage());

        $this->migrator = MigratorFactory::makeFromEvent($driver);
        $this->command = $driver->makeCommand(new Config(table: 'migration'));
    }

    /**
     * @throws Throwable
     */
    public function testUpExactlyAll(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up(new InputOptions(limit: 1));
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);

        $this->migrator->down();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        try {
            $this->migrator->up();
        } catch (ActionException) {
        }

        // только первая миграция успешно
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);

        $this->migrator->down();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        try {
            $this->migrator->up(new InputOptions(exactlyAll: true));
        } catch (ActionException) {
        }

        // всё или ничего
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);
    }

    public function testUpExactlyAllException(): void
    {
        $this->migrator->init();

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('up migrate error');

        $this->migrator->up(new InputOptions(exactlyAll: true));
    }

    /**
     * @throws Throwable
     */
    public function testVerify(): void
    {
        $this->migrator->init();
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);

        $this->migrator->up(new InputOptions(limit: 1));
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);

        // точность версионирования
        usleep(10_000);

        try {
            $this->migrator->verify();
        } catch (ActionException) {
        }

        // только первая миграция успешно
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
    }

    public function testVerifyException(): void
    {
        $this->migrator->init();

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('up migrate error');

        $this->migrator->verify();
    }
}
