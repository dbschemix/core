<?php

declare(strict_types=1);

namespace dbschemix\core\exception;

final class PrepareException extends MigratorException
{
    public function __construct(string $message)
    {
        parent::__construct(message: $message);
    }
}
