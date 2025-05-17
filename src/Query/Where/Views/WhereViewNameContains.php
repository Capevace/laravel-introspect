<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Generic\WhereTextContains;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereViewNameContains implements ViewWhere
{
    use WhereTextContains;

    /**
     * @param  string  $value
     */
    protected function getName($value): ?string
    {
        return $value;
    }
}
