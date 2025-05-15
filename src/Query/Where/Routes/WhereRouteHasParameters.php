<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteHasParameters implements RouteWhere
{
    use NotInverter;

    public function __construct(public array $parameters, public bool $not = false, public bool $all = true)
    {
    }

    public function filter(Route $value): bool
    {
        if ($this->all) {
            $passes = collect($this->parameters)
                ->every(fn(string $parameter) => $value->hasParameter($parameter));
        } else {
            $passes = collect($this->parameters)
                ->some(fn(string $parameter) => $value->hasParameter($parameter));
        }

        return $this->invert($passes, $this->not);
    }
}
