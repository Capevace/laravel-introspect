<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereUsedByView implements ViewWhere
{
    use NotInverter;

    public function __construct(public string $usedByView, public bool $not = false)
    {
    }

    public function filter(string $value): bool
    {
        // TODO: determine if the view is used by the

        return false;
    }
}
