<?php

namespace Mateffy\Introspect\Query\Where\Generic;

trait WhereTextEndsWith
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        return str_ends_with(haystack: $haystack, needle: $needle);
    }
}
