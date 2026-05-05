<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by path ending with text', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEndsWith(['posts']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports partial suffix matching', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEndsWith(['/posts']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathEndsWith(['nonexistent'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRoutePathEndsWith(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
