<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathEquals;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by exact path match', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEquals(['users/posts']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEquals(['users/*']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEquals(['nonexistent'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRoutePathEquals(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
