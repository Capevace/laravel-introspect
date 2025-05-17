<?php

namespace Mateffy\Introspect\Query\Where\Generic;

use Mateffy\Introspect\RegexHelper;

trait WhereTextContains
{
    use WhereTextComparison;

    protected function compare(?string $needle, ?string $haystack): bool
    {
        // If the string contains a wildcard, we replace it with a regex pattern (.*) and check for equality by matching the regex
        // otherwise we just check for equality
        if (str_contains($needle, '*')) {
            $pattern = RegexHelper::escape($needle);

            return preg_match('/'.$pattern.'/i', $haystack) === 1;
        } else {
            return str_contains(haystack: $haystack, needle: $needle);
        }
    }
}
