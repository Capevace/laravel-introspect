<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by description equals text
 */
class WhereTestDescriptionEquals implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $text,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $equals = $test->description === $this->text;

        return $this->invert($equals, $this->not);
    }
}
