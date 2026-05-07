<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;
use Mateffy\Introspect\Support\RegexHelper;

/**
 * Filter tests by file path pattern (glob)
 */
class WhereTestFileMatches implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $pattern,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $regex = RegexHelper::globToRegex($this->pattern);
        $matches = preg_match($regex, $test->location->file) === 1;

        return $this->invert($matches, $this->not);
    }
}
