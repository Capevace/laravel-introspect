<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Commands\WhereCommandDescriptionContains;
use Mateffy\Introspect\Query\Where\Commands\WhereCommandNameContains;
use Mateffy\Introspect\Query\Where\Commands\WhereCommandNameEndsWith;
use Mateffy\Introspect\Query\Where\Commands\WhereCommandNameEquals;
use Mateffy\Introspect\Query\Where\Commands\WhereCommandNameStartsWith;
use Mateffy\Introspect\Query\Where\Commands\WhereCommandSignatureContains;

trait WhereCommands
{
    public function whereNameStartsWith(string|array $text): static
    {
        $this->wheres->push(new WhereCommandNameStartsWith(
            is_array($text) ? $text : [$text],
            all: false
        ));

        return $this;
    }

    public function whereNameDoesntStartWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameStartsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEndsWith(string|array $text): static
    {
        $this->wheres->push(new WhereCommandNameEndsWith(
            is_array($text) ? $text : [$text],
            all: false
        ));

        return $this;
    }

    public function whereNameDoesntEndWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameEndsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameContains(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameContains(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntContain(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameContains(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEquals(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameEquals(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntEqual(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandNameEquals(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereSignatureContains(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandSignatureContains(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereSignatureDoesntContain(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandSignatureContains(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereDescriptionContains(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandDescriptionContains(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereDescriptionDoesntContain(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereCommandDescriptionContains(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }
}
