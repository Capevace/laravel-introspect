<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameContains;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by name containing text', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameContains(['index']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameContains(['users*']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameContains(['nonexistent'], not: true);
    $result = $where->filter($route);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteNameContains(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('returns false for routes without matching name', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameContains(['nonexistent']);
    $result = $where->filter($route);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('Users.Index');

    $where = new WhereRouteNameContains(['users'], caseless: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});
