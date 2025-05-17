<?php

namespace Mateffy\Introspect\Query\Where;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where;

interface RouteWhere extends Where
{
    /**
     * @param  Route  $value  The name of the route to filter
     */
    public function filter(Route $value): bool;
}
