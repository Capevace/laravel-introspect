<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by description containing text
 */
class WhereTestDescriptionContains implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $text,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $contains = str_contains(strtolower($test->description), strtolower($this->text));

        return $this->invert($contains, $this->not);
    }
}
