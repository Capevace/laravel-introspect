<?php

namespace Mateffy\Introspect\Query\Where\Generic;

trait WhereTextEquals
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        return $needle === $haystack;
    }
}
