<?php

use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

$totalViews = 32;

it('can query all views', function () use ($totalViews) {
    $views = introspect()
        ->views()
        ->get();

    expect($views)->toHaveCount($totalViews);
});

it('can query by name', function (string $text, string $method, int $count) use ($totalViews) {
    $query = introspect()->views();
    $oppositeQuery = introspect()->views();

    $views = (match ($method) {
        'contains' => $query->whereNameContains($text),
        'startsWith' => $query->whereNameStartsWith($text),
        'endsWith' => $query->whereNameEndsWith($text),
        'equals' => $query->whereNameEquals($text),
        'usedBy' => $query->whereUsedBy($text),
        'uses' => $query->whereUses($text),
        default => throw new InvalidArgumentException("Invalid method $method"),
    })->get();

    $oppositeViews = (match ($method) {
        'contains' => $oppositeQuery->whereNameDoesntContain($text),
        'startsWith' => $oppositeQuery->whereNameDoesntStartWith($text),
        'endsWith' => $oppositeQuery->whereNameDoesntEndWith($text),
        'equals' => $oppositeQuery->whereNameDoesntEqual($text),
        'usedBy' => $oppositeQuery->whereNotUsedBy($text),
        'uses' => $oppositeQuery->whereDoesntUse($text),
        default => throw new InvalidArgumentException("Invalid method $method"),
    })->get();

    $oppositeCount = $totalViews - $count;

    expect($views)
        ->toHaveCount($count, "Expected $method $text to return $count views, but got {$views->count()}")
        ->and($oppositeViews)
        ->toHaveCount($totalViews - $count, "Expected $method $text to return {$oppositeCount} views, but got {$oppositeViews->count()}");
})
    ->with([
        // contains
        ['non-existant', 'equals', 0],
        ['test', 'contains', 10],
        ['test2', 'contains', 1],
        ['workbench::components.wtf.test', 'contains', 8],
        ['workbench::components.wtf.*', 'contains', 8],
        ['workbench::*.test', 'contains', 8],
        ['workbench::*.test*', 'contains', 8],
        ['*wtf.test*', 'contains', 8],
        ['*wtf*test*', 'contains', 8],


        // equals
        ['non-existant', 'equals', 0],
        ['workbench::components.wtf.*', 'equals', 8],
        ['workbench::components.*.test', 'equals', 1],
        ['workbench::components.*.test*', 'equals', 8],
        ['workbench::components.*.test.*', 'equals', 0],
        ['workbench::*.test', 'equals', 1],
        ['workbench::*.test*', 'equals', 8],
        ['workbench::components.wtf.test*', 'equals', 8],
        ['workbench::components.wtf.test', 'equals', 1],
        ['workbench::components.wtf.test2', 'equals', 1],
        ['*wtf.test', 'equals', 1],
        ['*wtf.test*', 'equals', 8],

        // startsWith
        ['non-existant', 'startsWith', 0],
        ['workbench::components.wtf', 'startsWith', 8],
        ['workbench::components.wtf.', 'startsWith', 8],
        ['workbench::components.wtf.test', 'startsWith', 8],
        ['workbench::components.wtf.test2', 'startsWith', 1],

        // endsWith
        ['non-existant', 'endsWith', 0],
        ['.test', 'endsWith', 1],
        ['test', 'endsWith', 1],
        ['.wtf.test2', 'endsWith', 1],
        ['.wtf.test', 'endsWith', 1],

        // usedBy
        ['non-existant', 'usedBy', 0],
        ['workbench::test-welcome', 'usedBy', 7],
        ['workbench::test-welcome*', 'usedBy', 8],
        ['workbench::components.wtf.test', 'usedBy', 0],
        ['workbench::components.wtf.test*', 'usedBy', 0],
        ['workbench::components.wtf*', 'usedBy', 0],
        ['workbench::*test*', 'usedBy', 8],
        ['workbench::*test*', 'usedBy', 8],

        // uses
        ['non-existant', 'uses', 0],
        ['workbench::test-welcome', 'uses', 0],
        ['workbench::components.wtf.test', 'uses', 2],
        ['workbench::components.wtf.test2', 'uses', 1],
        ['workbench::components.wtf.test8', 'uses', 1],
        ['workbench::components.wtf.test*', 'uses', 2],
        ['workbench::components.wtf.test*', 'uses', 2],
        ['workbench::*.wtf.test*', 'uses', 2],
        // This checks that components. needs to be present, and "shorthand" view strings like <x-workbench::wtf.test don't work.
        // Instead, the full string needs to be used
        ['workbench::wtf.test', 'uses', 0],
        ['workbench::wtf.test*', 'uses', 0],
        ['workbench::*wtf.test*', 'uses', 2],
        ['*wtf.test*', 'uses', 2],
        ['*wtf.test', 'uses', 2],
        ['*wtf.test8', 'uses', 1],
    ]);


