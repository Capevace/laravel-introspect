<?php

namespace Mateffy\Introspect\Query\Contracts;

use Closure;
use Illuminate\Support\Collection;

interface QueryInterface
{
	/**
	 * @param Closure(QueryInterface): QueryInterface $query
	 */
	public function where(Closure $query, bool $or = false): static;

	public function createSubquery(): self;
}
