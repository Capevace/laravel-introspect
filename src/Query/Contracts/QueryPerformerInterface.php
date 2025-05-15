<?php

namespace Mateffy\Introspect\Query\Contracts;

use Illuminate\Support\Collection;

interface QueryPerformerInterface
{
    public function get(): Collection;
}
