<?php

declare(strict_types=1);

namespace dbschemix\core\package;

use OutOfBoundsException;
use Composer\InstalledVersions;

/**
 * @api
 * @param non-empty-string $package
 * @return non-empty-string
 * @throws OutOfBoundsException of package is not installed
 */
function version(string $package): string
{
    /**
     * @var array<non-empty-string, non-empty-string> $versions
     */
    static $versions = [];
    if (isset($versions[$package])) {
        return $versions[$package];
    }

    if (InstalledVersions::isInstalled($package)) {
        $version = InstalledVersions::getPrettyVersion($package);

        if ($version !== null) {
            assert($version !== '');

            return $versions[$package] = $version;
        }
    }

    throw new OutOfBoundsException("Package `$package` is not installed.");
}

/**
 * @api
 * @param non-empty-string $package
 * @return non-empty-string
 * @throws OutOfBoundsException of package is not installed
 */
function path(string $package): string
{
    $packagePath = InstalledVersions::getInstallPath($package);

    if ($packagePath !== null) {
        $filepath = realpath($packagePath);
        if ($filepath !== false) {
            /**
             * @var non-empty-string
             */
            return $filepath;
        }
    }

    throw new OutOfBoundsException("Package `$package` have a null install path.");
}
