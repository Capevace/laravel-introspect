<?php

namespace Mateffy\Introspect\Query\Builder;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameEndsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathEquals;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesController;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteHasParameters;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameContains;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameStartsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathContains;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathEndsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathStartsWith;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesMethods;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesMiddlewares;

trait WhereRoutes
{
    public function whereNameStartsWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameStartsWith(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntStartWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameStartsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEndsWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntEndWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameContains(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameContains(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntContain(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameContains(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function whereNameEquals(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function whereNameDoesntEqual(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteNameEndsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function wherePathEquals(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathEquals(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function wherePathDoesntEqual(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathEquals(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function wherePathEndsWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathEndsWith(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function wherePathDoesntEndWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathEndsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function wherePathStartsWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathStartsWith(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function wherePathDoesntStartWith(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathStartsWith(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

        return $this;
    }

    public function wherePathContains(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathContains(
            is_array($text) ? $text : [$text],
            all: $all
        ));

        return $this;
    }

    public function wherePathDoesntContain(string|array $text, bool $all = true): static
    {
        $this->wheres->push(new WhereRoutePathContains(
            is_array($text) ? $text : [$text],
            not: true,
            all: $all
        ));

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

//    public function whereUsesMethod(string $method): static;
//    public function whereDoesntUseMethod(string $method): static;
//    public function whereUsesMethods(array $methods, bool $all = true): static;
//    public function whereDoesntUseMethods(array $methods, bool $all = true): static;

    public function whereUsesMethod(string $method): static
    {
        $this->wheres->push(new WhereRouteUsesMethods([$method]));

        return $this;
    }

    public function whereDoesntUseMethod(string $method): static
    {
        $this->wheres->push(new WhereRouteUsesMethods([$method], not: true));

        return $this;
    }

    public function whereUsesMethods(array $methods, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteUsesMethods($methods, all: $all));

        return $this;
    }

    public function whereDoesntUseMethods(array $methods, bool $all = true): static
    {
        $this->wheres->push(new WhereRouteUsesMethods($methods, not: true, all: $all));

        return $this;
    }
}
