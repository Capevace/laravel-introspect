<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by name ending with text', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEndsWith(['index']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports partial suffix matching', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEndsWith(['.index']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEndsWith(['nonexistent'], not: true);
    $result = $where->filter($route);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteNameEndsWith(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('returns false for routes without matching suffix', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameEndsWith(['nonexistent']);
    $result = $where->filter($route);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.Index');

    $where = new WhereRouteNameEndsWith(['index'], caseless: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});
