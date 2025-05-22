<?php

namespace Mateffy\Introspect\Support;

use Mateffy\Introspect\Query\Contracts\PaginationInterface;

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
