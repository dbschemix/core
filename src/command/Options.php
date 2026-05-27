<?php

declare(strict_types=1);

namespace dbschemix\core\command;

use InvalidArgumentException;
use dbschemix\core\InputOptions;

/**
 * @api
 */
final readonly class Options
{
    /**
     * @param non-negative-int $limit
     * @param non-negative-int $version
     * @throws InvalidArgumentException
     */
    public function __construct(
        public int $limit = 0,
        public int $version = 0,
    ) {
        if ($limit < 0) {
            throw new InvalidArgumentException("limit must be non-negative, got $limit.");
        }
        if ($version < 0) {
            throw new InvalidArgumentException("version must be non-negative, got $version.");
        }
    }

    /**
     * @psalm-suppress MissingThrowsDocblock Inputs come from already-validated InputOptions.
     */
    public static function makeFromInput(InputOptions $args): self
    {
        return new self(
            limit: $args->limit,
            version: $args->version,
        );
    }

    /**
     * @param non-negative-int $version
     * @psalm-suppress MissingThrowsDocblock Limit comes from $this (already validated); version is constrained by signature.
     */
    public function withVersion(int $version): self
    {
        return new self(
            limit: $this->limit,
            version: $version,
        );
    }
}
