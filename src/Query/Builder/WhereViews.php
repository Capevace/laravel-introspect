<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Query\Where\Views\WhereUsedByView;
use Mateffy\Introspect\Query\Where\Views\WhereUsesView;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameContains;
use Mateffy\Introspect\Query\Where\Views\WhereViewNameEndsWith;
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
        $this->wheres->push(new WhereUsesView($view, not: false));

        return $this;
    }

    public function whereUsedBy(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsedByView($view));

        return $this;
    }

    public function whereNotUsedBy(array|string $view): ViewQueryInterface
    {
        $this->wheres->push(new WhereUsedByView($view, not: false));

        return $this;
    }

    public function whereNameStartsWith(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameStartsWith($text));

        return $this;
    }

    public function whereNameDoesntStartWith(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameStartsWith($text, not: true));

        return $this;
    }

    public function whereNameEndsWith(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEndsWith($text));

        return $this;
    }

    public function whereNameDoesntEndWith(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameEndsWith($text, not: true));

        return $this;
    }

    public function whereNameContains(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameContains($text));

        return $this;
    }

    public function whereNameDoesntContain(string $text): ViewQueryInterface
    {
        $this->wheres->push(new WhereViewNameContains($text, not: true));

        return $this;
    }
}
