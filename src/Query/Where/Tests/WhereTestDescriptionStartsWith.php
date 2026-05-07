<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where\Tests;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\TestWhere;

/**
 * Filter tests by description starts with text
 */
class WhereTestDescriptionStartsWith implements TestWhere
{
    use NotInverter;

    public function __construct(
        private string $text,
        private bool $not = false,
    ) {}

    public function filter(Test $test): bool
    {
        $startsWith = str_starts_with(strtolower($test->description), strtolower($this->text));

        return $this->invert($startsWith, $this->not);
    }
}
