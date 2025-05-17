<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Generic\WhereTextEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRoutePathEndsWith implements RouteWhere
{
    use WhereTextEndsWith;

    /**
     * @param  Route  $value
     */
    protected function getName($value): string
    {
        return $value->uri();
    }
}
