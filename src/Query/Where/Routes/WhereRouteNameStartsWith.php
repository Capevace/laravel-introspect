<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Generic\WhereTextStartsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteNameStartsWith implements RouteWhere
{
    use WhereTextStartsWith;

    /**
     * @param Route $value
     */
    protected function getName($value): string
    {
        return $value->getName();
    }
}
