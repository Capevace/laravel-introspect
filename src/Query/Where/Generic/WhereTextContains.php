<?php

namespace Mateffy\Introspect\Query\Where\Generic;

trait WhereTextContains
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        return str_contains(haystack: $haystack, needle: $needle);
    }
}
