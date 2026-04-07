<?php

declare(strict_types=1);

namespace dbschemix\core\connection;

interface ConnectionInterface extends StatementInterface
{
    public function beginTransaction(): TransactionInterface;
}
