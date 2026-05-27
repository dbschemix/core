<?php

declare(strict_types=1);

namespace dbschemix\core\connection;

/**
 * @api
 */
interface ConnectionInterface extends StatementInterface
{
    public function beginTransaction(): TransactionInterface;
}
