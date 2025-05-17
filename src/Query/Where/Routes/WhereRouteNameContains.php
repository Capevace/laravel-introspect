<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Generic\WhereTextContains;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteNameContains implements RouteWhere
{
    use WhereTextContains;

    /**
     * @param  Route  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
