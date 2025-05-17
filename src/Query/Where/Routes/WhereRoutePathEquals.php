<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Generic\WhereTextEquals;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRoutePathEquals implements RouteWhere
{
    use WhereTextEquals;

    /**
     * @param  Route  $value
     */
    protected function getName($value): string
    {
        return $value->uri();
    }
}
