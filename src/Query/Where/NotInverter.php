<?php

namespace Mateffy\Introspect\Query\Where;

trait NotInverter
{
    protected function invert(bool $condition, bool $not): bool
    {
        return $not ? !$condition : $condition;
    }
}
