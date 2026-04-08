<?php

declare(strict_types=1);

namespace dbschemix\core\event;

use Override;
use Throwable;

final readonly class ExceptionEvent implements EventInterface
{
    /**
     * @param non-empty-string $dbName
     */
    public function __construct(
        public string $dbName,
        public Throwable $exception,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return $this->dbName;
    }

    #[Override]
    public function getMessage(): string
    {
        /**
         * @var non-empty-string
         */
        return $this->exception->getMessage();
    }
}
