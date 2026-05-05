<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesMiddlewares;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by middleware', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->middleware('web');

    $where = new WhereRouteUsesMiddlewares(['web']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters routes by all specified middlewares', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->middleware(['web', 'auth']);

    $where = new WhereRouteUsesMiddlewares(['web', 'auth'], all: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters routes by any specified middleware', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->middleware(['web']);

    $where = new WhereRouteUsesMiddlewares(['web', 'auth'], all: false);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->middleware('web');

    $where = new WhereRouteUsesMiddlewares(['nonexistent'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteUsesMiddlewares(['web']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
