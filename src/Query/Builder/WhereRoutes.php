<?php

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesController;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteHasParameters;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameContains;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameEndsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameStartsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathContains;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathEndsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathStartsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesMiddlewares;

trait WhereRoutes
{
    public function whereNameStartsWith(string $text): static
    {
        $this->wheres->push(new WhereRouteNameStartsWith($text));

        return $this;
    }

    public function whereNameDoesntStartWith(string $text): static
    {
        $this->wheres->push(new WhereRouteNameStartsWith($text, not: true));

        return $this;
    }

    public function whereNameEndsWith(string $text): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith($text));

        return $this;
    }

    public function whereNameDoesntEndWith(string $text): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith($text, not: true));

        return $this;
    }

    public function whereNameContains(string $text): static
    {
        $this->wheres->push(new WhereRouteNameContains($text));

        return $this;
    }

    public function whereNameDoesntContain(string $text): static
    {
        $this->wheres->push(new WhereRouteNameContains($text, not: true));

        return $this;
    }

    public function wherePathStartsWith(string $text): static
    {
        $this->wheres->push(new WhereRoutePathStartsWith($text));

        return $this;
    }

    public function wherePathDoesntStartWith(string $text): static
    {
        $this->wheres->push(new WhereRoutePathStartsWith($text, not: true));

        return $this;
    }

    public function wherePathEndsWith(string $text): static
    {
        $this->wheres->push(new WhereRoutePathEndsWith($text));

        return $this;
    }

    public function wherePathDoesntEndWith(string $text): static
    {
        $this->wheres->push(new WhereRoutePathEndsWith($text, not: true));

        return $this;
    }

    public function wherePathContains(string $text): static
    {
        $this->wheres->push(new WhereRoutePathContains($text));

        return $this;
    }

    public function wherePathDoesntContain(string $text): static
    {
        $this->wheres->push(new WhereRoutePathContains($text, not: true));

        return $this;
    }

    public function whereUsesMiddleware(string $middleware): static
    {
        $this->wheres->push(new WhereRouteUsesMiddlewares([$middleware]));

        return $this;
    }

    public function whereUsesMiddlewares(array $middleware, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteUsesMiddlewares($middleware, all: $all));

        return $this;
    }

    public function whereDoesntUseMiddleware(string $middleware): static
    {
        $this->wheres->push(new WhereRouteUsesMiddlewares([$middleware], not: true));

        return $this;
    }

    public function whereDoesntUseMiddlewares(array $middleware, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteUsesMiddlewares($middleware, not: true, all: $all));

        return $this;
    }

    public function whereHasParameter(string $parameter): static
    {
        $this->wheres->push(new WhereRouteHasParameters([$parameter]));

        return $this;
    }

    public function whereDoesntHaveParameter(string $parameter): static
    {
        $this->wheres->push(new WhereRouteHasParameters([$parameter], not: true));

        return $this;
    }

    public function whereHasParameters(array $parameters, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteHasParameters($parameters, all: $all));

        return $this;
    }

    public function whereDoesntHaveParameters(array $parameters, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteHasParameters($parameters, not: true, all: $all));

        return $this;
    }

    public function whereUsesController(string $controller, ?string $method = null): static
    {
        $this->wheres->push(new WhereRouteUsesController($controller, method: $method));

        return $this;
    }

    public function whereDoesntUseController(string $controller, ?string $method = null): static
    {
        $this->wheres->push(new WhereRouteUsesController($controller, method: $method, not: true));

        return $this;
    }
}
