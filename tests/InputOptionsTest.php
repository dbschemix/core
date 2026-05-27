<?php

declare(strict_types=1);

namespace dbschemix\core\tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use dbschemix\core\InputOptions;

/**
 * @api InputOptions wither-методы и производные `hasApplyLatestVersion` / `hasRepeatable`.
 *
 * Withers возвращают новый InputOptions с частично перенесёнными полями. Состав «что переносим, что сбрасываем»
 * — намеренное бизнес-решение каждого вычитера, поэтому покрываем эту семантику явно.
 */
final class InputOptionsTest extends TestCase
{
    public function testDefaultsAreZeroAndFalse(): void
    {
        $options = new InputOptions();

        self::assertSame(0, $options->limit);
        self::assertSame(0, $options->version);
        self::assertFalse($options->dryRun);
        self::assertNull($options->dbName);
        self::assertNull($options->migrationName);
        self::assertFalse($options->exactlyAll);
        self::assertFalse($options->hasRepeatable());
        self::assertFalse($options->hasApplyLatestVersion());
    }

    public function testWithResetLimitClearsOnlyLimit(): void
    {
        $original = new InputOptions(
            limit: 5,
            version: 42,
            dryRun: true,
            dbName: 'mainDb',
            migrationName: 'add_users',
            exactlyAll: true,
            hasRepeatable: true,
            applyLatestVersion: true,
        );

        $next = $original->withResetLimit();

        // limit dropped to default
        self::assertSame(0, $next->limit);

        // everything else preserved
        self::assertSame(42, $next->version);
        self::assertTrue($next->dryRun);
        self::assertSame('mainDb', $next->dbName);
        self::assertSame('add_users', $next->migrationName);
        self::assertTrue($next->exactlyAll);

        // private flags survive — observed indirectly
        // hasRepeatable: true survives, but dryRun=true is also true, so hasRepeatable() returns false
        self::assertFalse($next->hasRepeatable());
        // applyLatestVersion: true survives; with version=42 (non-zero), hasApplyLatestVersion still returns false
        self::assertFalse($next->hasApplyLatestVersion());

        // original is not mutated
        self::assertSame(5, $original->limit);
    }

    public function testWithVersionSetsVersionAndDropsLimitAndFlags(): void
    {
        $original = new InputOptions(
            limit: 5,
            version: 1,
            dryRun: true,
            dbName: 'mainDb',
            migrationName: 'add_users',
            exactlyAll: true,
            hasRepeatable: true,
            applyLatestVersion: true,
        );

        $next = $original->withVersion(42);

        // explicit set
        self::assertSame(42, $next->version);

        // preserved
        self::assertTrue($next->dryRun);
        self::assertSame('mainDb', $next->dbName);
        self::assertSame('add_users', $next->migrationName);

        // dropped to defaults — withVersion only forwards (version, dryRun, dbName, migrationName)
        self::assertSame(0, $next->limit);
        self::assertFalse($next->exactlyAll);
        // hasRepeatable was true; not forwarded
        self::assertFalse($next->hasRepeatable());
        // applyLatestVersion was true; not forwarded
        self::assertFalse($next->hasApplyLatestVersion());

        // original is not mutated
        self::assertSame(1, $original->version);
    }

    public function testWithExactlyAllSetsExactlyAllAndDropsApplyLatestVersion(): void
    {
        $original = new InputOptions(
            limit: 5,
            version: 42,
            dryRun: true,
            dbName: 'mainDb',
            migrationName: 'add_users',
            exactlyAll: false,
            hasRepeatable: true,
            applyLatestVersion: true,
        );

        $next = $original->withExactlyAll();

        // explicit set
        self::assertTrue($next->exactlyAll);

        // preserved
        self::assertSame(5, $next->limit);
        self::assertSame(42, $next->version);
        self::assertTrue($next->dryRun);
        self::assertSame('mainDb', $next->dbName);
        self::assertSame('add_users', $next->migrationName);

        // private flags: hasRepeatable preserved; applyLatestVersion dropped — withExactlyAll forwards hasRepeatable but not applyLatestVersion
        // hasRepeatable: true forwarded, but dryRun=true so hasRepeatable() returns false
        self::assertFalse($next->hasRepeatable());
        // applyLatestVersion dropped + version=42 makes it false anyway
        self::assertFalse($next->hasApplyLatestVersion());

        // original is not mutated
        self::assertFalse($original->exactlyAll);
    }

    public function testWithersReturnNewInstances(): void
    {
        $original = new InputOptions(limit: 5);

        self::assertNotSame($original, $original->withResetLimit());
        self::assertNotSame($original, $original->withVersion(1));
        self::assertNotSame($original, $original->withExactlyAll());
    }

    /**
     * @return iterable<string, array{applyLatestVersion: bool, version: int, limit: int, want: bool}>
     */
    public static function hasApplyLatestVersionCases(): iterable
    {
        yield 'all false: not requested' => [
            'applyLatestVersion' => false,
            'version' => 0,
            'limit' => 0,
            'want' => false,
        ];
        yield 'requested, no version, no limit: yes' => [
            'applyLatestVersion' => true,
            'version' => 0,
            'limit' => 0,
            'want' => true,
        ];
        yield 'requested, version set: no (explicit version overrides)' => [
            'applyLatestVersion' => true,
            'version' => 42,
            'limit' => 0,
            'want' => false,
        ];
        yield 'requested, limit=1: no (single-step migration excludes latest-aggregate)' => [
            'applyLatestVersion' => true,
            'version' => 0,
            'limit' => 1,
            'want' => false,
        ];
        yield 'requested, limit>1: yes' => [
            'applyLatestVersion' => true,
            'version' => 0,
            'limit' => 5,
            'want' => true,
        ];
    }

    /**
     * @param non-negative-int $version
     * @param non-negative-int $limit
     */
    #[DataProvider('hasApplyLatestVersionCases')]
    public function testHasApplyLatestVersion(
        bool $applyLatestVersion,
        int $version,
        int $limit,
        bool $want,
    ): void {
        $options = new InputOptions(
            limit: $limit,
            version: $version,
            applyLatestVersion: $applyLatestVersion,
        );

        self::assertSame($want, $options->hasApplyLatestVersion());
    }

    /**
     * @return iterable<string, array{hasRepeatable: bool, dryRun: bool, want: bool}>
     */
    public static function hasRepeatableCases(): iterable
    {
        yield 'not requested' => [
            'hasRepeatable' => false,
            'dryRun' => false,
            'want' => false,
        ];
        yield 'requested, not dry-run: yes' => [
            'hasRepeatable' => true,
            'dryRun' => false,
            'want' => true,
        ];
        yield 'requested, dry-run: no (dry-run inhibits repeatable replay)' => [
            'hasRepeatable' => true,
            'dryRun' => true,
            'want' => false,
        ];
        yield 'not requested, dry-run: no' => [
            'hasRepeatable' => false,
            'dryRun' => true,
            'want' => false,
        ];
    }

    #[DataProvider('hasRepeatableCases')]
    public function testHasRepeatable(bool $hasRepeatable, bool $dryRun, bool $want): void
    {
        $options = new InputOptions(
            dryRun: $dryRun,
            hasRepeatable: $hasRepeatable,
        );

        self::assertSame($want, $options->hasRepeatable());
    }
}
