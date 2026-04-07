<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use Override;
use Throwable;
use PHPUnit\Framework\TestCase;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\command\Options;
use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestStorage;
use dbschemix\core\Config;
use dbschemix\core\Context;

/**
 * Отдельно слой команд, без мигратора.
 */
final class CommandTest extends TestCase
{
    private CommandInterface $command;

    #[Override]
    protected function setUp(): void
    {
        $driver = new TestDriver(new TestStorage());
        $this->command = $driver->makeCommand(new Config(table: 'migration'));
    }

    /**
     * @throws Throwable
     */
    public function testUp(): void
    {
        $this->execInitialization();

        $this->execUp('table1');

        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table1']);
    }

    /**
     * @throws Throwable
     */
    public function testDown(): void
    {
        $this->execInitialization();

        $this->execUp('table1');
        $this->execUp('table2');

        $data = $this->command->fetchApplied();
        self::assertCount(2, $data);
        self::assertNotEmpty($data['test-table1']);
        self::assertNotEmpty($data['test-table2']);

        $this->execDown('table1');
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table2']);

        $this->execDown('table2');
        $data = $this->command->fetchApplied();
        self::assertEmpty($data);
    }

    /**
     * @throws Throwable
     */
    public function testFetchLimit(): void
    {
        $this->execInitialization();

        $this->execUp('table1');
        $this->execUp('table2');
        $this->execUp('table3');

        $data = $this->command->fetchApplied(
            new Options(limit: 1)
        );
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table3']);

        $data = $this->command->fetchApplied(
            new Options(limit: 2)
        );
        self::assertCount(2, $data);

        // sort order
        $names = array_keys($data);
        self::assertEquals('test-table3', $names[0]);
        self::assertEquals('test-table2', $names[1]);
    }

    /**
     * @throws Throwable
     */
    public function testFetchVersion(): void
    {
        $this->execInitialization();

        $this->execUp('table1', 111);
        $this->execUp('table2', 111);
        $this->execUp('table3', 222);

        $data = $this->command->fetchApplied(
            new Options(version: 111)
        );
        self::assertCount(2, $data);
        self::assertNotEmpty($data['test-table1']);
        self::assertNotEmpty($data['test-table2']);

        $data = $this->command->fetchApplied(
            new Options(version: 222)
        );
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table3']);

        // сомнительный кейс, но допускаем
        $data = $this->command->fetchApplied(
            new Options(limit: 1, version: 111)
        );
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table2']);
    }

    /**
     * @throws Throwable
     */
    public function testUpDryRun(): void
    {
        $this->execInitialization();

        $response = $this->command->up(
            new Context(
                dbName: 'test',
                filename: 'test',
                query: '--test',
                dryRun: false,
            )
        );

        self::assertTrue($response);

        $response = $this->command->up(
            new Context(
                dbName: 'test',
                filename: 'test',
                query: '--test',
                dryRun: true,
            )
        );

        self::assertFalse($response);
    }

    /**
     * @throws Throwable
     */
    public function testDownDryRun(): void
    {
        $this->execInitialization();

        $response = $this->command->down(
            new Context(
                dbName: 'test',
                filename: 'test',
                query: '--test',
                dryRun: false,
            )
        );

        self::assertTrue($response);

        $response = $this->command->down(
            new Context(
                dbName: 'test',
                filename: 'test',
                query: '--test',
                dryRun: true,
            )
        );

        self::assertFalse($response);
    }

    /**
     * @throws Throwable
     */
    public function testUpError(): void
    {
        $this->execInitialization();

        $this->execUp('table1');
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table1']);

        try {
            $this->execFailQuery();
        } catch (Throwable) {
        }

        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table1']);
    }

    /**
     * @throws Throwable
     */
    public function testDownError(): void
    {
        $this->execInitialization();

        $this->execUp('table1');
        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table1']);

        try {
            $this->execFailQuery();
        } catch (Throwable) {
        }

        $data = $this->command->fetchApplied();
        self::assertCount(1, $data);
        self::assertNotEmpty($data['test-table1']);
    }

    /**
     * @throws Throwable
     */
    private function execInitialization(): void
    {
        $queryString = <<<SQL
CREATE TABLE IF NOT EXISTS migration
(
    name TEXT PRIMARY KEY,
    version INT DEFAULT 0,
    atime TEXT
);
SQL;
        $this->command->exec(
            new Context(
                dbName: 'test',
                filename: 'setup.sql',
                query: $queryString,
            )
        );
    }

    /**
     * @param non-negative-int $version
     * @throws Throwable
     */
    private function execUp(string $tableName, int $version = 1): void
    {
        $queryString = <<<SQL
CREATE TABLE IF NOT EXISTS $tableName
(
    name TEXT PRIMARY KEY
)
SQL;
        $this->command->up(
            new Context(
                dbName: 'test',
                filename: 'test-' . $tableName,
                query: $queryString,
                version: $version,
            )
        );
    }

    /**
     * @throws Throwable
     */
    private function execDown(string $tableName): void
    {
        $queryString = <<<SQL
DROP TABLE IF EXISTS $tableName
SQL;
        $this->command->down(
            new Context(
                dbName: 'test',
                filename: 'test-' . $tableName,
                query: $queryString,
            )
        );
    }

    /**
     * @throws Throwable
     */
    private function execFailQuery(): void
    {
        $queryString = <<<SQL
DROP TABLE IF EXISTS migration
SQL;
        $this->command->down(
            new Context(
                dbName: 'test',
                filename: 'test_error.sql',
                query: $queryString,
            )
        );
    }
}
