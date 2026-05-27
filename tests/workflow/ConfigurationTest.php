<?php

declare(strict_types=1);

namespace dbschemix\core\tests\workflow;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use dbschemix\core\exception\ConnectionException;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\exception\InitializationException;
use dbschemix\core\tests\stub\TestDriverError;
use dbschemix\core\tests\stub\TestDriver;
use dbschemix\core\tests\stub\TestStorage;
use dbschemix\core\tests\MigratorFactory;
use dbschemix\core\Config;
use dbschemix\core\InputOptions;
use dbschemix\core\MigratorInterface;

/**
 * Вероятные ошибки при конфигурировании приложения.
 */
final class ConfigurationTest extends TestCase
{
    private MigratorInterface $migrator;

    #[Override]
    protected function setUp(): void
    {
        $this->migrator = MigratorFactory::makeFromEvent(
            new TestDriver(new TestStorage())
        );
    }

    public function testConnectionException(): void
    {
        $migrator = MigratorFactory::makeFromEvent(
            new TestDriverError()
        );

        $this->expectException(ConnectionException::class);

        $migrator->init();
    }

    public function testUpInitializationException(): void
    {
        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('Error reading system data.');

        $this->migrator->up();
    }

    public function testDownInitializationException(): void
    {
        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('Error reading system data.');

        $this->migrator->down();
    }

    public function testFixtureException(): void
    {
        $this->migrator->init();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches(
            '/^the directory .+ does not exist.$/i'
        );

        $this->migrator->fixture();
    }

    public function testCreateDbNameException(): void
    {
        $this->migrator->init();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('DBName must be declared.');

        $this->migrator->create();
    }

    public function testCreateMigrationNameException(): void
    {
        $this->migrator->init();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Migration Name must be declared.');

        $this->migrator->create(new InputOptions(dbName: 'test'));
    }

    /**
     * @return iterable<non-empty-string[]>
     */
    public static function additionTableNameCases(): iterable
    {
        yield ['migrate'];
        yield ['migrate_table'];
        yield ['migrate22'];
    }

    /**
     * @param non-empty-string $tableName
     */
    #[DataProvider('additionTableNameCases')]
    public function testTableName(string $tableName): void
    {
        $config = new Config(table: $tableName);
        self::assertNotEmpty($config);
    }

    /**
     * @return iterable<non-empty-string[]>
     */
    public static function additionTableNameBadCases(): iterable
    {
        yield ['`migrate`'];
        yield ['migrate space'];
        yield ['migrate;'];
        yield ['{}'];
    }

    /**
     * @param non-empty-string $tableName
     */
    #[DataProvider('additionTableNameBadCases')]
    public function testTableNameException(string $tableName): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('contains invalid characters.');

        new Config(table: $tableName);
    }
}
