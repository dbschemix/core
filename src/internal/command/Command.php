<?php

declare(strict_types=1);

namespace dbschemix\core\internal\command;

use Override;
use Throwable;
use dbschemix\core\command\CommandInterface;
use dbschemix\core\command\Options;
use dbschemix\core\connection\ConnectionInterface;
use dbschemix\core\Config;
use dbschemix\core\Context;

/**
 * @psalm-internal dbschemix\core
 */
final readonly class Command implements CommandInterface
{
    public function __construct(
        private ConnectionInterface $connection,
        private Config $config,
    ) {
    }

    #[Override]
    public function fetchApplied(Options $options = new Options()): array
    {
        $params = [];

        $where = '';
        if ($options->version > 0) {
            $where = 'WHERE version=:version';
            $params['version'] = $options->version;
        }

        $limit = '';
        if ($options->limit > 0) {
            $limit = 'LIMIT ' . $options->limit;
        }

        $query = 'SELECT name, version FROM '
            . $this->config->table
            . ($where !== '' ? ' ' . $where : '')
            . ' ORDER BY atime DESC, name DESC'
            . ($limit !== '' ? ' ' . $limit : '');

        return $this->connection->fetchRecord($query, $params);
    }

    #[Override]
    public function up(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        $transaction = $this->connection->beginTransaction();

        try {
            $transaction->exec($context->query);
            $transaction->exec(
                sprintf('INSERT INTO %s (name, version, atime) VALUES (:name, :version, :atime)', $this->config->table),
                [
                    'name' => $context->filename,
                    'version' => $context->version,
                    'atime' => gmdate('Y-m-d H:i:s'),
                ],
            );
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return $transaction->commit();
    }

    #[Override]
    public function down(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        $transaction = $this->connection->beginTransaction();

        try {
            $transaction->exec($context->query);
            $transaction->exec(
                sprintf('DELETE FROM %s WHERE name=:name', $this->config->table),
                [
                    'name' => $context->filename,
                ],
            );
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return $transaction->commit();
    }

    #[Override]
    public function exec(Context $context): bool
    {
        if ($context->dryRun) {
            return false;
        }

        $transaction = $this->connection->beginTransaction();

        try {
            $transaction->exec($context->query);
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return $transaction->commit();
    }
}
