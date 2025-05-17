<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Generic\WhereTextEndsWith;
use Mateffy\Introspect\Query\Where\ViewWhere;

class WhereViewNameEndsWith implements ViewWhere
{
    use WhereTextEndsWith;

    /**
     * @param  string  $value
     */
    protected function getName($value): ?string
    {
        return $value;
    }
}
