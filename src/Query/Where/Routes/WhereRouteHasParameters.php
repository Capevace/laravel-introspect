<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteHasParameters implements RouteWhere
{
    use NotInverter;

    /**
     * @var Collection<string> $parameters
     */
    public Collection $parameters;

    /**
     * @param string[]|Collection<string> $parameters
     */
    public function __construct(array|Collection $parameters, public bool $not = false, public bool $all = true)
    {
        $this->parameters = collect($parameters);
    }

    public function filter(Route $value): bool
    {
        if ($this->all) {
            $passes = $this->parameters->every(fn (string $parameter) => $value->hasParameter($parameter));
        } else {
            $passes = $this->parameters->some(fn (string $parameter) => $value->hasParameter($parameter));
        }

        return $this->invert($passes, $this->not);
    }
}
