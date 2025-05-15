<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface Query
{
    public function limit(int $limit): static;
    public function offset(int $limit): static;
    public function paginate(int $limit, int $offset): LengthAwarePaginator;
    public function get(): Collection;
}
