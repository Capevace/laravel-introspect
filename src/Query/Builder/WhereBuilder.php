<?php

namespace Mateffy\Introspect\Query\Builder;

use Closure;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Contracts\QueryInterface;
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
	 * @param Closure(QueryInterface): QueryInterface $query
     */
	public function where(Closure $query, bool $or = false): static
	{
        $subquery = $this->createSubquery();

        if ($returned = $query($subquery)) {
            $subquery = $returned;
        }

		$where = new NestedWhere(wheres: $subquery->wheres, or: $or);

		$this->wheres->push($where);

		return $this;
	}
}
