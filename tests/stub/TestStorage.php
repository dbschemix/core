<?php

declare(strict_types=1);

namespace dbschemix\core\tests\stub;

use RuntimeException;

final class TestStorage
{
    /**
     * @var array<non-empty-string, non-negative-int>
     */
    private array $table = [];

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $memory = [];

    /**
     * @param non-negative-int $limit
     * @param non-negative-int $version
     *
     * @return array<non-empty-string, non-negative-int>
     */
    public function getMigration(int $limit = 0, int $version = 0): array
    {
        $data = array_reverse($this->table);

        if ($limit === 0 && $version === 0) {
            return $data;
        }

        $count = 0;
        $result = [];

        foreach ($data as $name => $storedVersion) {
            if ($version > 0 && $storedVersion !== $version) {
                continue;
            }

            $result[$name] = $storedVersion;

            ++$count;

            if ($limit > 0 && $count >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param non-empty-string $key
     * @param non-negative-int $version
     * @throws RuntimeException
     */
    public function saveMigration(string $key, int $version): void
    {
        if (isset($this->table[$key])) {
            throw new RuntimeException(
                sprintf('Migration "%s" already exists.', $key)
            );
        }

        $this->table[$key] = $version;
    }

    /**
     * @param non-empty-string $key
     */
    public function dropMigration(string $key): void
    {
        unset($this->table[$key]);
    }

    /**
     * @param non-empty-string $key
     */
    public function get(string $key): ?string
    {
        return $this->memory[$key] ?? null;
    }

    /**
     * @param non-empty-string $key
     * @param non-empty-string $value
     */
    public function set(string $key, string $value): void
    {
        $this->memory[$key] = $value;
    }
}
