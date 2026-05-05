<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameEquals;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by exact name match', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEquals(['users.index']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEquals(['users.*']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEquals(['nonexistent'], not: true);
    $result = $where->filter($route);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteNameEquals(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('returns false for routes without matching name', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEquals(['other.route']);
    $result = $where->filter($route);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('Users.Index');

    $where = new WhereRouteNameEquals(['users.index'], caseless: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});
