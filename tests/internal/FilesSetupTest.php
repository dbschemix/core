<?php

/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace dbschemix\core\tests\internal;

use PHPUnit\Framework\TestCase;
use dbschemix\core\exception\ConfigurationException;
use dbschemix\core\internal\filesystem\Setup;

final class FilesSetupTest extends TestCase
{
    public function testSimple(): void
    {
        $fs = new Setup(dirname(__DIR__) . '/migration/postgres/setup/  ', 'test');
        self::assertTrue($fs->all()->valid());

        foreach ($fs->all() as $filename => $sql) {
            self::assertEquals('setup.sql', $filename);
            self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "test"', $sql);
        }
    }

    public function testDirNotExists(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/^the directory .+ does not exist.$/i');

        $fs = new Setup(dirname(__DIR__) . '/migration/postgres/not-exists', 'test');
        $fs->all()->valid();
    }
}
