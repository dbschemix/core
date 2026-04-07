<?php

declare(strict_types=1);

namespace dbschemix\core\tests\stub;

use Override;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\connection\DriverInterface;
use dbschemix\core\Config;

final readonly class TestDriver implements DriverInterface
{
    public function __construct(private TestStorage $storage)
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
    public function makeCommand(Config $config): CommandInterface
    {
        return new TestCommand($this->storage);
    }
}
