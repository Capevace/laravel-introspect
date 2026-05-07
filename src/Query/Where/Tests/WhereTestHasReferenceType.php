<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by reference type presence
 */
class WhereTestHasReferenceType implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $type,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        if ($test->metadata === null || $test->metadata->references === null) {
            return $this->invert(false, $this->not);
        }

        foreach ($test->metadata->references as $ref) {
            if ($ref->type === $this->type) {
                return $this->invert(true, $this->not);
            }
        }

        return $this->invert(false, $this->not);
    }
}
