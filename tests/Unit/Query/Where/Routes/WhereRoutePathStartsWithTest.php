<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRoutePathStartsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by path starting with text', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathStartsWith(['users']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports partial prefix matching', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathStartsWith(['users/']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users/posts', function () {});

    $where = new WhereRoutePathStartsWith(['nonexistent'], not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRoutePathStartsWith(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
