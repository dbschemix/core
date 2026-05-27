<?php

declare(strict_types=1);

namespace dbschemix\core\exception;

use Throwable;

/**
 * @api
 */
final class InitializationException extends MigratorException
{
    public function __construct(string $message, Throwable $previous)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}
