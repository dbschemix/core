<?php

declare(strict_types=1);

namespace dbschemix\core\tests\stub;

use Override;
use RuntimeException;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\command\Options;
use dbschemix\core\Context;

final readonly class TestCommand implements CommandInterface
{
    public function __construct(
        private TestStorage $storage,
    ) {
    }

    #[Override]
    public function fetchApplied(Options $options = new Options()): array
    {
        if ($this->storage->get('setup.sql') === null) {
            throw new RuntimeException('Migrator not initialization.');
        }

        return $this->storage->getMigration($options->limit, $options->version);
    }

    #[Override]
    public function up(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        if (str_ends_with($context->filename, '_error.sql')) {
            throw new RuntimeException('up migrate error');
        }

        $this->storage->set($context->filename, $context->query);
        $this->storage->saveMigration($context->filename, $context->version);
        return true;
    }

    #[Override]
    public function down(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        if (str_ends_with($context->filename, '_error.sql')) {
            throw new RuntimeException('down migrate error');
        }


        $this->storage->set($context->filename, $context->query);
        $this->storage->dropMigration($context->filename);
        return true;
    }

    #[Override]
    public function exec(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        $this->storage->set($context->filename, $context->query);
        return true;
    }
}
