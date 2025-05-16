<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteUsesMiddlewares implements RouteWhere
{
    use NotInverter;

    public Collection $middlewares;

    public function __construct(array|Collection $middlewares, public bool $not = false, public bool $all = true)
    {
        $this->middlewares = collect($middlewares)
            ->map(fn(string $middleware) => strtolower($middleware))
            ->unique()
            ->values();
    }

    public function filter(Route $value): bool
    {
        $middlewares = collect($value->gatherMiddleware() ?? [])
            ->map(fn(string $middleware) => strtolower($middleware))
            ->unique()
            ->values();

        if ($this->all) {
            $passes = collect($this->middlewares)
                ->every(fn(string $middleware) => in_array($middleware, $middlewares->all()));
        } else {
            $passes = collect($this->middlewares)
                ->some(fn(string $middleware) => in_array($middleware, $middlewares->all()));
        }

        return $this->invert($passes, $this->not);
    }
}
