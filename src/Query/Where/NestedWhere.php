<?php

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\QueryInterface;
use Mateffy\Introspect\Query\Where;
use Roave\BetterReflection\Reflection\ReflectionClass;

class NestedWhere implements Where, QueryInterface
{
    use NotInverter;
	use WhereBuilder;

	public function __construct(bool $or = false, public bool $not = false)
	{
		$this->or = $or;
		$this->wheres = collect();
	}

	public function filter(ReflectionClass $value): bool
	{
        $results = $this->wheres
            ->map(fn (Where $where) => $where->filter($value));

        return $this->invert(
            condition: $this->or
                ? $results->contains(true)
                : $results->every(fn (bool $result) => $result),
            not: $this->not
        );
	}
}
