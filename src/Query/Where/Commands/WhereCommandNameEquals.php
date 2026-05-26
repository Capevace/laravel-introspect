<?php

namespace Mateffy\Introspect\Query\Where\Commands;

use Mateffy\Introspect\DTO\Command;
use Mateffy\Introspect\Query\Where\CommandWhere;
use Mateffy\Introspect\Query\Where\Generic\WhereTextEquals;

class WhereCommandNameEquals implements CommandWhere
{
    use WhereTextEquals;

    protected function getName($value): ?string
    {
        return $value->name;
    }
}
