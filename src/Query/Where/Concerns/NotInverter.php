<?php

namespace Mateffy\Introspect\Query\Where\Concerns;

trait NotInverter
{
    protected function invert(bool $condition, bool $not): bool
    {
        return $not ? !$condition : $condition;
    }
}
