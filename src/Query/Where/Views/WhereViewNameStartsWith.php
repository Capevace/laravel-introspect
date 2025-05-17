<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Generic\WhereTextStartsWith;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereViewNameStartsWith implements ViewWhere
{
    use WhereTextStartsWith;

    /**
     * @param  string  $value
     */
    protected function getName($value): ?string
    {
        return $value;
    }
}
