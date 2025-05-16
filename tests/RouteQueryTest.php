<?php

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

if (!function_exists('introspect')) {
    function introspect(): \Mateffy\Introspect\LaravelIntrospect
    {
        return Introspect::codebase('/Users/mat/Projects/testbench', ['app', 'resources/views']);
    }
}

it('can query all routes', function () {
    $routes = introspect()
        ->routes()
        ->get();

    dd($routes->map(fn ($route) => $route->uri()));
});
