<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathContains;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by path containing text', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathContains(['posts']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathContains(['user*']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathContains(['nonexistent'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRoutePathContains(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
