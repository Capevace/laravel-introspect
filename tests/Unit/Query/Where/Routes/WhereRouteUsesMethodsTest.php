<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesMethods;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by HTTP methods', function () {
    $route = new Route(['GET', 'POST'], 'users', function () {});

    $where = new WhereRouteUsesMethods(['GET']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters routes by all specified methods', function () {
    $route = new Route(['GET', 'POST'], 'users', function () {});

    $where = new WhereRouteUsesMethods(['GET', 'POST'], all: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters routes by any specified method', function () {
    $route = new Route(['GET'], 'users', function () {});

    $where = new WhereRouteUsesMethods(['GET', 'POST'], all: false);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});

    $where = new WhereRouteUsesMethods(['POST'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteUsesMethods(['GET']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
