<?php

declare(strict_types=1);

namespace dbschemix\core\internal\filesystem;

/**
 * @param non-empty-string $path
 * @return non-empty-string
 * @infection-ignore-all
 */
function normalizePath(string $path): string
{
    return rtrim(trim($path), '/') . '/';
}

/**
 * @param non-empty-string $path
 * @param non-empty-string $suffix
 * @return non-empty-string
 * @infection-ignore-all
 */
function joinSuffix(string $path, string $suffix): string
{
    return rtrim(trim($path), '/') . rtrim($suffix, '/') . '/';
}

/**
 * @param non-empty-string $path
 * @param non-empty-string $filename
 * @return non-empty-string
 * @infection-ignore-all
 */
function joinFilename(string $path, string $filename): string
{
    return rtrim(trim($path), '/') . '/' . $filename;
}
