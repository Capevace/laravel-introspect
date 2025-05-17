<?php

namespace Mateffy\Introspect;

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
}
