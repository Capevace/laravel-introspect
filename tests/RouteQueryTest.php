<?php

use Workbench\App\Controllers\TestController;
use Workbench\App\Controllers\UnusedTestController;

$totalRoutes = 4;

it('can query all routes', function () use ($totalRoutes) {
    $routes = introspect()
        ->routes()
        ->get();

    expect($routes)->toHaveCount($totalRoutes);
});

it('can query for controllers', function (string $controller, int $count) {
    $routes = introspect()
        ->routes()
        ->whereUsesController($controller)
        ->get();

    expect($routes)->toHaveCount($count);

    if ($count === 0) {
        return;
    }

    expect($routes->first()?->getController()::class)->toBe($controller);
})
    ->with([
        [TestController::class, 1],
        [UnusedTestController::class, 0],
    ]);

it('can query for controllers (not)', function (string $controller, int $count) {
    $routes = introspect()
        ->routes()
        ->whereDoesntUseController($controller)
        ->get();

    expect($routes)->toHaveCount($count);
})
    ->with([
        [TestController::class, $totalRoutes - 1],
        [UnusedTestController::class, $totalRoutes],
    ]);

it('can query for methods', function (array $methods, int $count) {
    $routes = introspect()
        ->routes()
        ->whereUsesMethods($methods)
        ->get();

    expect($routes)->toHaveCount($count, message: "Expected {$count} routes for methods: ".implode(', ', $methods));
})
    ->with([
        [['post'], 2],
        [['get'], 3],
        [['delete'], 0],
        [['post', 'get'], 1],
        [['post', 'delete'], 0],
        [['get', 'delete'], 0],
    ]);

it('can query for methods (not)', function (array $methods, int $count) {
    $routes = introspect()
        ->routes()
        ->whereDoesntUseMethods($methods)
        ->get();

    expect($routes)->toHaveCount($count, message: "Expected {$count} routes for methods: ".implode(', ', $methods));
})
    ->with([
        [['post'], $totalRoutes - 2],
        [['get'], $totalRoutes - 3],
        [['delete'], $totalRoutes],
        [['post', 'get'], $totalRoutes - 1],
        [['post', 'delete'], $totalRoutes],
        [['get', 'delete'], $totalRoutes],
    ]);

it('can query for controllers and method', function (string $controller, string $method, int $count) {
    $routes = introspect()
        ->routes()
        ->whereUsesController($controller)
        ->whereUsesMethod($method)
        ->get();

    expect($routes)->toHaveCount($count);

    if ($count === 0) {
        return;
    }

    expect($routes->first()?->getController()::class)->toBe($controller);
})
    ->with([
        [TestController::class, 'post', 1],
        [TestController::class, 'delete', 0],
    ]);

it('can query for parameters', function (array $parameters, int $count) {
    $routes = introspect()
        ->routes()
        ->whereHasParameters($parameters)
        ->get();

    expect($routes)->toHaveCount($count, message: "Expected {$count} routes for parameters: ".implode(', ', $parameters));
})
    ->with([
        [['param1'], 1],
        [['param2'], 2],
        [['param3'], 1],
        [['param1', 'param2'], 1],
        [['param1', 'param3'], 0],
        [['param2', 'param3'], 1],
    ]);

it('can query for parameters (not)', function (array $parameters, int $count) {
    $routes = introspect()
        ->routes()
        ->whereDoesntHaveParameters($parameters)
        ->get();

    expect($routes)->toHaveCount($count, message: "Expected {$count} routes for parameters: ".implode(', ', $parameters));
})
    ->with([
        [['param1'], $totalRoutes - 1],
        [['param2'], $totalRoutes - 2],
        [['param3'], $totalRoutes - 1],
        [['param1', 'param2'], $totalRoutes - 1],
        [['param1', 'param3'], $totalRoutes],
        [['param2', 'param3'], $totalRoutes - 1],
    ]);

it('can query for name contains', function (string $text, string $method, int $count) {
    $query = introspect()
        ->routes();

    $routes = (match ($method) {
        'contains' => $query->whereNameContains($text),
        'doesntContain' => $query->whereNameDoesntContain($text),
        'startsWith' => $query->whereNameStartsWith($text),
        'doesntStartWith' => $query->whereNameDoesntStartWith($text),
        'endsWith' => $query->whereNameEndsWith($text),
        'doesntEndWith' => $query->whereNameDoesntEndWith($text),
    })->get();

    expect($routes)->toHaveCount($count, message: "Expected {$count} routes for name {$method}: {$text}");
})
    ->with([
        ['test', 'contains', 2],
        ['test', 'doesntContain', $totalRoutes - 2],
        ['test', 'startsWith', 2],
        ['test', 'doesntStartWith', $totalRoutes - 2],
        ['test', 'endsWith', 1],
        ['test', 'doesntEndWith', $totalRoutes - 1],
        ['single-action', 'contains', 1],
        ['single-action', 'doesntContain', $totalRoutes - 1],
        ['single-action', 'startsWith', 0],
        ['single-action', 'doesntStartWith', $totalRoutes],
        ['single-action', 'endsWith', 1],
        ['single-action', 'doesntEndWith', $totalRoutes - 1],
    ]);
