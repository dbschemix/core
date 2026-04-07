<?php

declare(strict_types=1);

namespace dbschemix\core\tests\stub;

use Override;
use RuntimeException;
use dbschemix\core\connection\DriverInterface;
use dbschemix\core\exception\ConnectionException;
use dbschemix\core\Config;

final readonly class TestDriverError implements DriverInterface
{
    public function __construct()
    {
    }

    #[Override]
    public function getName(): string
    {
        return 'test';
    }

    public function getSourceName(): string
    {
        return 'storage';
    }

    #[Override]
    public function getSetupPath(): string
    {
        return dirname(__DIR__) . '/migration/test/setup';
    }

    #[Override]
    public function makeCommand(Config $config): never
    {
        throw new ConnectionException($this, new RuntimeException('Connection error'));
    }
}
