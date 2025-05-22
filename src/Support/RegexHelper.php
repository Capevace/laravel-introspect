<?php

namespace Mateffy\Introspect\Support;

class RegexHelper
{
    public static function escape(string $pattern): string
    {
        return str($pattern)
            ->replace('\\', '\\\\') // Make sure to escape backslashes first! Otherwise the ones we add will be escaped too
            ->replace('.', '\\.')
            ->replace('+', '\\+')
            ->replace('?', '\\?')
            ->replace('(', '\\(')
            ->replace(')', '\\)')
            ->replace('[', '\\[')
            ->replace(']', '\\]')
            ->replace('{', '\\{')
            ->replace('}', '\\}')
            ->replace('^', '\\^')
            ->replace('$', '\\$')
            ->replace('|', '\\|')
            ->replace('/', '\\/')
            ->replace('*', '.*')
            ->toString();
    }

    public static function matches(string $needle, array $texts)
    {
        foreach ($texts as $text) {
            // If the string contains a wildcard, we replace it with a regex pattern (.*) and check for equality by matching the regex
            // otherwise we just check for equality
            if (str_contains($needle, '*')) {
                $pattern = RegexHelper::escape($needle);

                $matches = preg_match('/^'.$pattern.'$/i', $text) === 1;
            } else {
                $matches = $needle === $text;
            }

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
