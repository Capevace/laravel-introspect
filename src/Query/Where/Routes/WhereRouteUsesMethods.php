<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteUsesMethods implements RouteWhere
{
    use NotInverter;

    public Collection $methods;

    public function __construct(array|Collection $methods, public bool $not = false, public bool $all = true)
    {
        $this->methods = collect($methods)
            ->map(fn(string $method) => strtoupper($method))
            ->unique()
            ->values();
    }

    public function filter(Route $value): bool
    {
        $methods = collect($value->methods())
            ->map(fn(string $method) => strtoupper($method))
            ->unique()
            ->values();

        if ($this->all) {
            $passes = collect($this->methods)
                ->every(fn(string $method) => in_array($method, $methods->all()));
        } else {
            $passes = collect($this->methods)
                ->some(fn(string $method) => in_array($method, $methods->all()));
        }

        return $this->invert($passes, $this->not);
    }
}
