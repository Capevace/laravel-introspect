<?php

namespace Mateffy\Introspect\Query\Where\Routes;

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\RouteWhere;

class WhereRouteUsesController implements RouteWhere
{
    use NotInverter;

    public function __construct(public string $controller, public ?string $method = null, public bool $not = false)
    {
        // Normally we can only match the action method using the class name when using single action controllers,
        // but for better readability we allow the user to specify __invoke as the method name.
        if ($this->method === '__invoke') {
            $this->method = $controller;
        }
    }

    public function filter(Route $value): bool
    {
        $isController = $value->getControllerClass() === $this->controller;
        $isMethod = ! $this->method || ($value->getActionMethod() === $this->method);

        return $this->invert($isController && $isMethod, $this->not);
    }
}
