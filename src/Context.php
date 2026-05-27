<?php

declare(strict_types=1);

namespace dbschemix\core;

use InvalidArgumentException;

/**
 * @api
 * @infection-ignore-all IncrementInteger
 */
final readonly class Context
{
    /**
     * @param non-empty-string $dbName
     * @param non-empty-string $filename
     * @param non-empty-string $query
     * @param non-negative-int $version
     * @throws InvalidArgumentException
     * @psalm-suppress TypeDoesNotContainType, InvalidCast Runtime defense for callers that bypass static type checks.
     */
    public function __construct(
        public string $dbName,
        public string $filename,
        public string $query,
        public int $version = 0,
        public bool $dryRun = false,
    ) {
        if ($dbName === '') {
            throw new InvalidArgumentException('dbName must be a non-empty string.');
        }
        if ($filename === '') {
            throw new InvalidArgumentException('filename must be a non-empty string.');
        }
        if ($query === '') {
            throw new InvalidArgumentException('query must be a non-empty string.');
        }
        if ($version < 0) {
            throw new InvalidArgumentException("version must be non-negative, got {$version}.");
        }
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->dbName . '/' . $this->filename;
    }
}
