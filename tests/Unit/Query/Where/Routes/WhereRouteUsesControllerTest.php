<?php

use Illuminate\Routing\Route;
use Mateffy\Introspect\Query\Where\Routes\WhereRouteUsesController;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Workbench\App\Controllers\TestController;

it('filters routes by controller class', function () {
    $route = new Route(['GET'], 'users', [TestController::class, 'index']);

    $where = new WhereRouteUsesController(TestController::class);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters with exact controller match', function () {
    $route = new Route(['GET'], 'users', [TestController::class, 'index']);

    $where = new WhereRouteUsesController(TestController::class);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('filters by controller with specific method', function () {
    $route = new Route(['GET'], 'users', [TestController::class, 'index']);

    $where = new WhereRouteUsesController(TestController::class, method: 'index');
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $route = new Route(['GET'], 'users', function () {});

    $where = new WhereRouteUsesController('NonExistentController', not: true);
    $result = $where->filter($route);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereRouteUsesController('TestController');

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
