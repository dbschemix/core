<?php

declare(strict_types=1);

namespace dbschemix\core\tests\internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function dbschemix\core\internal\filesystem\joinFilename;
use function dbschemix\core\internal\filesystem\joinSuffix;
use function dbschemix\core\internal\filesystem\normalizePath;

/**
 * Чистые трансформеры путей в src/internal/filesystem/functions.php.
 */
final class PathFunctionsTest extends TestCase
{
    /**
     * @return iterable<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function normalizePathCases(): iterable
    {
        yield 'absolute, no trailing slash' => ['/var/migrations', '/var/migrations/'];
        yield 'absolute, single trailing slash' => ['/var/migrations/', '/var/migrations/'];
        yield 'absolute, multiple trailing slashes' => ['/var/migrations///', '/var/migrations/'];
        yield 'relative, no trailing slash' => ['app/migrations', 'app/migrations/'];
        yield 'leading whitespace stripped' => ['  /var/m', '/var/m/'];
        yield 'trailing whitespace stripped' => ['/var/m   ', '/var/m/'];
        yield 'whitespace and slash together' => ['  /var/m/  ', '/var/m/'];
        yield 'root path' => ['/', '/'];
    }

    /**
     * @param non-empty-string $input
     * @param non-empty-string $expected
     */
    #[DataProvider('normalizePathCases')]
    public function testNormalizePath(string $input, string $expected): void
    {
        self::assertSame($expected, normalizePath($input));
    }

    /**
     * @return iterable<string, array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}>
     */
    public static function joinSuffixCases(): iterable
    {
        yield 'simple suffix' => ['/var/m', '-fixture', '/var/m-fixture/'];
        yield 'path with trailing slash, plain suffix' => ['/var/m/', '-repeatable', '/var/m-repeatable/'];
        yield 'suffix with trailing slash stripped' => ['/var/m', '-fixture/', '/var/m-fixture/'];
        yield 'both with trailing slashes' => ['/var/m/', '-fixture/', '/var/m-fixture/'];
        yield 'whitespace in path stripped' => ['  /var/m  ', '-x', '/var/m-x/'];
    }

    /**
     * @param non-empty-string $path
     * @param non-empty-string $suffix
     * @param non-empty-string $expected
     */
    #[DataProvider('joinSuffixCases')]
    public function testJoinSuffix(string $path, string $suffix, string $expected): void
    {
        self::assertSame($expected, joinSuffix($path, $suffix));
    }

    /**
     * @return iterable<string, array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}>
     */
    public static function joinFilenameCases(): iterable
    {
        yield 'plain' => ['/var/m', 'file.sql', '/var/m/file.sql'];
        yield 'path with trailing slash' => ['/var/m/', 'file.sql', '/var/m/file.sql'];
        yield 'path with multiple trailing slashes' => ['/var/m///', 'file.sql', '/var/m/file.sql'];
        yield 'whitespace in path stripped' => ['  /var/m  ', 'file.sql', '/var/m/file.sql'];
        yield 'filename with timestamp prefix' => ['/var/m', '202501011024_entity.sql', '/var/m/202501011024_entity.sql'];
    }

    /**
     * @param non-empty-string $path
     * @param non-empty-string $filename
     * @param non-empty-string $expected
     */
    #[DataProvider('joinFilenameCases')]
    public function testJoinFilename(string $path, string $filename, string $expected): void
    {
        self::assertSame($expected, joinFilename($path, $filename));
    }
}
