<?php

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\Query\Where;
use Mateffy\Introspect\DTO\Command;

interface CommandWhere extends Where
{
    public function filter(Command $value): bool;
}
