<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by case status
 */
class WhereTestStatusEquals implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $status,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $matches = $test->caseStatus === $this->status;

        return $this->invert($matches, $this->not);
    }
}
