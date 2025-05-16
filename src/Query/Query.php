<?php

namespace Mateffy\Introspect\Query;

use Closure;
use Illuminate\Support\Collection;

interface Query
{
	/**
	 * @param Closure(Query): Query $query
	 */
	public function where(Closure $query, bool $or = false): static;

	public function createSubquery(): self;

    public function limit(int $limit): static;
    public function offset(int $limit): static;
//    public function paginate(int $limit, int $offset): LengthAwarePaginator;
    public function get(): Collection;
}
