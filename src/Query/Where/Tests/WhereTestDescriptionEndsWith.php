<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by description ends with text
 */
class WhereTestDescriptionEndsWith implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $text,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $endsWith = str_ends_with(strtolower($test->description), strtolower($this->text));

        return $this->invert($endsWith, $this->not);
    }
}
