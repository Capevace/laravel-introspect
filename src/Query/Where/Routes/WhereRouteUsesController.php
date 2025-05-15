<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteUsesController implements RouteWhere
{
    use NotInverter;

    public function __construct(public string $controller, public ?string $method = null, public bool $not = false)
    {
    }

    public function filter(Route $value): bool
    {
        $isController = $value->getControllerClass() === $this->controller;
        $isMethod = !$this->method || $value->getActionMethod() === $this->method;

        return $this->invert($isController && $isMethod, $this->not);
    }
}
