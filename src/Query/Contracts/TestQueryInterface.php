<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Contracts;

use Mateffy\Introspect\Query\Query;

/**
 * Interface for test queries
 */
interface TestQueryInterface extends Query
{
    /**
     * Filter by file path pattern (glob)
     */
    public function whereFileMatches(string $pattern): static;

    /**
     * Exclude files matching pattern
     */
    public function whereFileDoesntMatch(string $pattern): static;

    /**
     * Filter by test description equals text
     */
    public function whereDescriptionEquals(string $text): static;

    /**
     * Filter by test description not equals text
     */
    public function whereDescriptionDoesntEqual(string $text): static;

    /**
     * Filter by test description starts with text
     */
    public function whereDescriptionStartsWith(string $text): static;

    /**
     * Filter by test description not starts with text
     */
    public function whereDescriptionDoesntStartWith(string $text): static;

    /**
     * Filter by test description ends with text
     */
    public function whereDescriptionEndsWith(string $text): static;

    /**
     * Filter by test description not ends with text
     */
    public function whereDescriptionDoesntEndWith(string $text): static;

    /**
     * Filter by test description containing text
     */
    public function whereDescriptionContains(string $text): static;

    /**
     * Filter by test description not containing text
     */
    public function whereDescriptionDoesntContain(string $text): static;

    /**
     * Filter by case status
     */
    public function whereStatusEquals(string $status): static;

    /**
     * Filter by case status not equal
     */
    public function whereStatusDoesntEqual(string $status): static;

    /**
     * Filter by tests that have a specific tag
     */
    public function whereHasTag(string $tag): static;

    /**
     * Filter by tests that don't have a specific tag
     */
    public function whereDoesntHaveTag(string $tag): static;

    /**
     * Filter by tests that have a specific reference type
     */
    public function whereHasReferenceType(string $type): static;

    /**
     * Set test directory to scan (relative to base path)
     */
    public function inTestDirectory(string $directory): static;

    /**
     * Set app directory to scan for colocated tests
     */
    public function inAppDirectory(string $directory): static;

    /**
     * Include line numbers in results (requires nikic/php-parser)
     */
    public function withLineNumbers(bool $withLineNumbers = true): static;

    /**
     * Set file pattern for test files (default: *.php)
     */
    public function withPattern(string $pattern): static;
}
