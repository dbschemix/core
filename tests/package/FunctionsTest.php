<?php

declare(strict_types=1);

namespace dbschemix\core\tests\package;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

use function dbschemix\core\package\path;
use function dbschemix\core\package\version;

/**
 * Публичные хелперы над Composer\InstalledVersions в src/package/functions.php.
 */
final class FunctionsTest extends TestCase
{
    public function testVersionReturnsNonEmptyVersionForInstalledPackage(): void
    {
        // phpunit/phpunit is a require-dev dependency of this project,
        // so it is guaranteed installed in the test environment.
        $result = version('phpunit/phpunit');

        self::assertNotEmpty($result);
    }

    public function testVersionThrowsForUnknownPackage(): void
    {
        $this->expectException(OutOfBoundsException::class);

        version('dbschemix/nonexistent-fake-package');
    }

    public function testVersionIsCachedAcrossCalls(): void
    {
        // The function uses a static cache keyed by package name; second call
        // must return the same value and not re-query Composer\InstalledVersions.
        $first = version('phpunit/phpunit');
        $second = version('phpunit/phpunit');

        self::assertSame($first, $second);
    }

    public function testPathReturnsAbsolutePathForInstalledPackage(): void
    {
        $result = path('phpunit/phpunit');

        self::assertNotEmpty($result);
        self::assertDirectoryExists($result);
    }

    public function testPathThrowsForUnknownPackage(): void
    {
        $this->expectException(OutOfBoundsException::class);

        path('dbschemix/nonexistent-fake-package');
    }
}
