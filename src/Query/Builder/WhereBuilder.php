<?php

namespace Mateffy\Introspect\Query\Builder;

use Closure;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Query;
use Mateffy\Introspect\Query\Where;
use Mateffy\Introspect\Query\Where\NestedWhere;

trait WhereBuilder
{
	public bool $or = false;

	/**
	 * @var Collection<Where>
	 */
	protected Collection $wheres;

    /**
	 * @param Closure(Query): Query $query
     */
	public function where(Closure $query, bool $or = false, bool $not = false): static
	{
        $subquery = $this->createSubquery();

        if ($returned = $query($subquery)) {
            $subquery = $returned;
        }

		$where = new NestedWhere(wheres: $subquery->wheres, or: $or, not: $not);

		$this->wheres->push($where);

		return $this;
	}

    /**
	 * @param Closure(Query): Query $query
     */
	public function or(Closure $query, bool $not = false): static
	{
        return $this->where($query, or: true, not: $not);
	}

    /**
	 * @param Closure(Query): Query $query
     */
	public function and(Closure $query, bool $not = false): static
	{
        return $this->where($query, not: $not);
    }
}
