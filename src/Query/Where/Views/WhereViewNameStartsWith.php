<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereViewNameStartsWith implements ViewWhere
{
    use NotInverter;

    public function __construct(public string $text, public bool $not = false)
    {
    }

    public function filter(string $value): bool
    {
        return $this->invert(str_starts_with($value, $this->text), $this->not);
    }
}
