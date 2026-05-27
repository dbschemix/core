<?php

declare(strict_types=1);

namespace dbschemix\core;

use InvalidArgumentException;

/**
 * @api
 * @infection-ignore-all
 */
final readonly class InputOptions
{
    /**
     * @param non-negative-int $limit
     * @param non-negative-int $version
     * @param ?non-empty-string $dbName
     * @param ?non-empty-string $migrationName
     * @throws InvalidArgumentException
     * @psalm-suppress TypeDoesNotContainType, InvalidCast Runtime defense for callers that bypass static type checks.
     */
    public function __construct(
        public int $limit = 0,
        public int $version = 0,
        public bool $dryRun = false,
        public ?string $dbName = null,
        public ?string $migrationName = null,
        public bool $exactlyAll = false,
        private bool $hasRepeatable = false,
        private bool $applyLatestVersion = false,
    ) {
        if ($limit < 0) {
            throw new InvalidArgumentException("limit must be non-negative, got {$limit}.");
        }
        if ($version < 0) {
            throw new InvalidArgumentException("version must be non-negative, got {$version}.");
        }
        if ($dbName === '') {
            throw new InvalidArgumentException('dbName must be null or a non-empty string.');
        }
        if ($migrationName === '') {
            throw new InvalidArgumentException('migrationName must be null or a non-empty string.');
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Inputs come from $this and are already validated.
     */
    public function withResetLimit(): self
    {
        return new self(
            version: $this->version,
            dryRun: $this->dryRun,
            dbName: $this->dbName,
            migrationName: $this->migrationName,
            exactlyAll: $this->exactlyAll,
            hasRepeatable: $this->hasRepeatable,
            applyLatestVersion: $this->applyLatestVersion,
        );
    }

    /**
     * @param non-negative-int $version
     * @psalm-suppress MissingThrowsDocblock Inputs come from $this and are already validated.
     */
    public function withVersion(int $version): self
    {
        return new self(
            version: $version,
            dryRun: $this->dryRun,
            dbName: $this->dbName,
            migrationName: $this->migrationName,
        );
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Inputs come from $this and are already validated.
     */
    public function withExactlyAll(): self
    {
        return new self(
            limit: $this->limit,
            version: $this->version,
            dryRun: $this->dryRun,
            dbName: $this->dbName,
            migrationName: $this->migrationName,
            exactlyAll: true,
            hasRepeatable: $this->hasRepeatable,
        );
    }

    public function hasApplyLatestVersion(): bool
    {
        return $this->applyLatestVersion
            && $this->version === 0
            && ($this->limit === 0 || $this->limit > 1);
    }

    public function hasRepeatable(): bool
    {
        return $this->hasRepeatable && $this->dryRun === false;
    }
}
