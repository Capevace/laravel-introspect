<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Generic\WhereTextEquals;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereViewNameEquals implements ViewWhere
{
    use WhereTextEquals;

    /**
     * @param  string  $value
     */
    protected function getName($value): ?string
    {
        return $value;
    }
}
