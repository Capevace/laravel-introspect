<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteHasParameters;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes having all specified parameters', function () {
    $route = new Route(['GET'], 'users/{id}/posts/{slug}', function () {});

    $where = new WhereRouteHasParameters(['id', 'slug'], all: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters routes having any specified parameters', function () {
    $route = new Route(['GET'], 'users/{id}', function () {});

    $where = new WhereRouteHasParameters(['id', 'nonexistent'], all: false);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});

    $where = new WhereRouteHasParameters(['nonexistent'], not: true);
    $result = $where->filter($route);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteHasParameters(['id']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('returns false for routes without matching parameters', function () {
    $route = new Route(['GET'], 'users', function () {});

    $where = new WhereRouteHasParameters(['nonexistent']);
    $result = $where->filter($route);

    expect($result)->toBeFalse();
});
