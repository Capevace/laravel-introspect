<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteNameStartsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;

it('filters routes by name starting with text', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameStartsWith(['users']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('supports partial prefix matching', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameStartsWith(['users.']);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameStartsWith(['nonexistent'], not: true);
    $result = $where->filter($route);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteNameStartsWith(['test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('returns false for routes without matching prefix', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('users.index');

    $where = new WhereRouteNameStartsWith(['other']);
    $result = $where->filter($route);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $route = new Route(['GET'], 'users', function () {});
    $route->name('Users.Index');

    $where = new WhereRouteNameStartsWith(['users'], caseless: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});
