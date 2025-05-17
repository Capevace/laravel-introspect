<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Classes\WhereExtendsClass;
use Mateffy\Introspect\Query\Where\Classes\WhereImplementsInterfaces;
use Mateffy\Introspect\Query\Where\Classes\WhereUsesTraits;

trait WhereClasses
{
    public function whereExtends(string $classpath): self
    {
        $this->wheres->push(new WhereExtendsClass($classpath));

        return $this;
    }

    public function whereDoesntExtend(string $classpath): self
    {
        $this->wheres->push(new WhereExtendsClass($classpath, not: true));

        return $this;
    }

    public function whereImplements(string|array $interface): self
    {
        $this->wheres->push(new WhereImplementsInterfaces(is_array($interface) ? $interface : [$interface]));

        return $this;
    }

    public function whereDoesntImplement(string|array $interface): self
    {
        $this->wheres->push(new WhereImplementsInterfaces(
            is_array($interface) ? $interface : [$interface],
            not: true
        ));

        return $this;
    }

    public function whereUses(string|array $trait): self
    {
        $this->wheres->push(new WhereUsesTraits(is_array($trait) ? $trait : [$trait]));

        return $this;
    }

    public function whereDoesntUse(string|array $trait): self
    {
        $this->wheres->push(new WhereUsesTraits(
            is_array($trait) ? $trait : [$trait],
            not: true
        ));

        return $this;
    }
}
