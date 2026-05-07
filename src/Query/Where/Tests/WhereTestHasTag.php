<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by tag presence
 */
class WhereTestHasTag implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $tag,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        if ($test->metadata === null || $test->metadata->tags === null) {
            return $this->invert(false, $this->not);
        }

        $hasTag = in_array($this->tag, $test->metadata->tags, true);

        return $this->invert($hasTag, $this->not);
    }
}
