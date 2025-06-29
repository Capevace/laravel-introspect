<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Classes\WhereClassNameContains;
use Mateffy\Introspect\Query\Where\Classes\WhereClassNameEndsWith;
use Mateffy\Introspect\Query\Where\Classes\WhereClassNameEquals;
use Mateffy\Introspect\Query\Where\Classes\WhereClassNameStartsWith;
use Mateffy\Introspect\Query\Where\Classes\WhereExtendsClass;
use Mateffy\Introspect\Query\Where\Classes\WhereImplementsInterfaces;
use Mateffy\Introspect\Query\Where\Classes\WhereUsesTraits;

trait WhereClasses
{
    public function whereExtends(string|array $classpath, bool $all = true): self
    {
        $this->wheres->push(new WhereExtendsClass(
            is_array($classpath) ? $classpath : [$classpath],
            all: $all
        ));

        return $this;
    }

    public function whereDoesntExtend(string|array $classpath, bool $all = true): self
    {
        $this->wheres->push(new WhereExtendsClass(
            is_array($classpath) ? $classpath : [$classpath],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereImplements(string|array $interface, bool $all = true): self
    {
        $this->wheres->push(new WhereImplementsInterfaces(is_array($interface) ? $interface : [$interface], all: $all));

        return $this;
    }

    public function whereDoesntImplement(string|array $interface, bool $all = true): self
    {
        $this->wheres->push(new WhereImplementsInterfaces(
            is_array($interface) ? $interface : [$interface],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereUses(string|array $trait, bool $all = true): self
    {
        $this->wheres->push(new WhereUsesTraits(is_array($trait) ? $trait : [$trait], all: $all));

        return $this;
    }

    public function whereDoesntUse(string|array $trait, bool $all = true): self
    {
        $this->wheres->push(new WhereUsesTraits(
            is_array($trait) ? $trait : [$trait],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameContains(string|array $text, bool $all = true): self
    {
        $this->wheres->push(new WhereClassNameContains(
            texts: is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntContain(string|array $text, bool $all = true): self
    {
        $this->wheres->push(new WhereClassNameContains(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEquals(string|array $text): self
    {
        $this->wheres->push(new WhereClassNameEquals(
            texts: is_array($text) ? $text : [$text],
            all: false
        ));

        return $this;
    }

    public function whereNameDoesntEqual(string|array $text, bool $all = true): self
    {
        $this->wheres->push(new WhereClassNameEquals(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all,
        ));

        return $this;
    }

    public function whereNameStartsWith(string|array $text): self
    {
        $this->wheres->push(new WhereClassNameStartsWith(
            texts: is_array($text) ? $text : [$text],
            all: false,
        ));

        return $this;
    }

    public function whereNameDoesntStartWith(string|array $text, bool $all = true): self
    {
        $this->wheres->push(new WhereClassNameStartsWith(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all,
        ));

        return $this;
    }

    public function whereNameEndsWith(string|array $text): self
    {
        $this->wheres->push(new WhereClassNameEndsWith(
            texts: is_array($text) ? $text : [$text],
            all: false,
        ));

        return $this;
    }

    public function whereNameDoesntEndWith(string|array $text, bool $all = true): self
    {
        $this->wheres->push(new WhereClassNameEndsWith(
            texts: is_array($text) ? $text : [$text],
            not: true,
            all: $all,
        ));

        return $this;
    }
}
