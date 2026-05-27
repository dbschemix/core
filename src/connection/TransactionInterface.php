<?php

declare(strict_types=1);

namespace dbschemix\core\connection;

/**
 * @api
 */
interface TransactionInterface extends StatementInterface
{
    public function isActive(): bool;

    public function commit(): bool;

    public function rollback(): bool;
}
