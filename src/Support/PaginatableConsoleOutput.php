<?php

namespace Mateffy\Introspect\Support;

use Closure;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use function Laravel\Prompts\warning;

trait PaginatableConsoleOutput
{
    public function paginate(PaginationInterface $query): PaginationInterface
    {
        $offset = $this->option('offset', null);
        $limit = $this->option('limit', null);

        if ($offset !== null) {
            $query->offset($offset);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query;
    }
}
