<?php

namespace Mateffy\Introspect\Query\Contracts;

interface PaginationInterface
{
    public function limit(int $limit): static;

    public function offset(int $offset): static;
}
