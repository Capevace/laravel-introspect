<?php

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\Query\Where;

interface ViewWhere extends Where
{
    /**
     * @param  view-string  $value  The name of the view to filter
     */
    public function filter(string $value): bool;
}
