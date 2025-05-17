<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Query\Where\Views\WhereUsedByView;
use Mateffy\Introspect\Query\Where\Views\WhereUsesView;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameContains;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameEndsWith;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameEquals;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameStartsWith;

trait WhereViews
{
    public function whereUses(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsesView($view));

        return $this;
    }

    public function whereDoesntUse(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsesView($view, not: true));

        return $this;
    }

    public function whereUsedBy(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsedByView($view));

        return $this;
    }

    public function whereNotUsedBy(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsedByView($view, not: true));

        return $this;
    }

    public function whereNameStartsWith(string|array $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameStartsWith(
            texts: is_array($text) ? $text : [$text],
            all: false
        ));

        return $this;
    }

    public function whereNameDoesntStartWith(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameStartsWith(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEndsWith(string|array $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEndsWith(
            texts: is_array($text) ? $text : [$text],
            all: false
        ));

        return $this;
    }

    public function whereNameDoesntEndWith(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEndsWith(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameContains(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameContains(
            texts: is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntContain(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameContains(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEquals(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEquals(
            texts: is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntEqual(string|array $text, bool $all = true): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEquals(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }
}
