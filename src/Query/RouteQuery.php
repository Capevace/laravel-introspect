<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Builder\WhereBuilder;
use Mateffy\Introspect\Query\Builder\WhereRoutes;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;

class RouteQuery implements PaginationInterface, QueryPerformerInterface, RouteQueryInterface
{
    use WhereBuilder;
    use WhereRoutes;
    use WithPagination;

    public function __construct(
        protected string $path,
    ) {
        $this->wheres = collect();
    }

    public function createSubquery(): self
    {
        return new RouteQuery(path: $this->path);
    }

    public function get(): Collection
    {
        /** @var Router $router */
        $router = app('router');

        return collect($router->getRoutes())
            ->filter(fn (Route $route) => $this->filterUsingQuery($route))
            ->values()
            ->map(fn (Route $route) => $this->transformResult($route));
    }

    protected function transformResult(Route $route): Route
    {
        return $route;
    }

    public function filterUsingQuery(Route $route): bool
    {
        return $this->wheres->every(fn (Where $where) => $where->filter($route));
    }
}
