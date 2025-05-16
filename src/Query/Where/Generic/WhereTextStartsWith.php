<?php

namespace Mateffy\Introspect\Query\Where\Generic;

trait WhereTextStartsWith
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        return str_starts_with(haystack: $haystack, needle: $needle);
    }
}
