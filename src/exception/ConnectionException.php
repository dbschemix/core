<?php

declare(strict_types=1);

namespace dbschemix\core\exception;

use Throwable;
use dbschemix\core\connection\DriverInterface;

/**
 * @api
 */
final class ConnectionException extends MigratorException
{
    public function __construct(DriverInterface $driver, Throwable $previous)
    {
        parent::__construct(
            message: sprintf('%s:%s', $driver->getName(), $previous->getMessage()),
            previous: $previous,
        );
    }
}
