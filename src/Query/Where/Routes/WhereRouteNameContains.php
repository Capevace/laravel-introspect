<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteNameContains implements RouteWhere
{
    use NotInverter;

    public function __construct(public string $text, public bool $not = false)
    {
    }

    public function filter(Route $value): bool
    {
        return $this->invert(str_contains($value->getName(), $this->text), $this->not);
    }
}
