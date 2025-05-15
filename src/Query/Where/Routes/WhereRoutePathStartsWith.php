<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRoutePathStartsWith implements RouteWhere
{
    use NotInverter;

    public function __construct(public string $text, public bool $not = false)
    {
    }

    public function filter(Route $value): bool
    {
        return $this->invert(str_starts_with($value->uri(), $this->text), $this->not);
    }
}
