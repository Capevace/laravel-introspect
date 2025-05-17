<?php

namespace Mateffy\Introspect\Query\Where\Generic;

trait WhereTextContains
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        // If the string contains a wildcard, we replace it with a regex pattern (.*) and check for equality by matching the regex
        // otherwise we just check for equality
        if (str_contains($haystack, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($haystack, '/'));

            return preg_match('/' . $pattern . '/i', $needle) === 1;
        } else {
            return str_contains(haystack: $haystack, needle: $needle);
        }
    }
}
