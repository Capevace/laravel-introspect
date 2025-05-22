<?php

use Laravel\Prompts\Prompt;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

$totalViews = 32;

it('can query views with JSON output', function () {
    $views = introspect()->views()->get();

    expect($this->artisan('introspect:views', ['--format' => 'json']))
        ->expectsOutput($views->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
        ->assertSuccessful();
});

it('can get the count', function () use ($totalViews) {
    expect($this->artisan('introspect:views', ['--count' => true]))
        ->expectsOutput($totalViews)
        ->assertSuccessful();
});

it('can query views with filters', function (array $params, Closure $query, int $count) use ($totalViews) {
    $views = $query(introspect()->views())->get();

    expect($views->count())->toBe($count);

    expect($this->artisan('introspect:views', ['--format' => 'json', ...$params]))
        ->expectsOutput($views->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
        ->assertSuccessful();

    expect($this->artisan('introspect:views', ['--count' => true, ...$params]))
        ->expectsOutput($views->count())
        ->assertSuccessful();
})
    ->with([
//        [
//            [],
//            fn (ViewQueryInterface $query) => $query,
//            47,
//        ],
        [
            ['--name' => '*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameEquals('*test*'),
            10,
        ],
        [
            ['--name' => '*components*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameEquals('*components*test*'),
            8,
        ],
        [
            ['--name' => '*test'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameEquals('*test'),
            1,
        ],
        [
            ['--used-by' => 'workbench::test-welcome'],
            fn (ViewQueryInterface $query) => $query
                ->whereUsedBy('workbench::test-welcome'),
            7,
        ],
        [
            ['--used-by' => 'workbench::test-welcome2'],
            fn (ViewQueryInterface $query) => $query
                ->whereUsedBy('workbench::test-welcome2'),
            2,
        ],
        [
            ['--used-by' => 'workbench::*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereUsedBy('workbench::*test*'),
            8
        ],
        [
            ['--uses' => 'workbench::*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereUses('workbench::*test*'),
            2,
        ],
        [
            ['--doesnt-use' => 'workbench::*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereDoesntUse('workbench::*test*'),
            $totalViews - 2,
        ],
        [
            ['--not-used-by' => 'workbench::*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNotUsedBy('workbench::*test*'),
            $totalViews - 8,
        ],
        [
            ['--name-not' => '*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*test*'),
            $totalViews - 10,
        ],
        [
            ['--name-not' => '*components*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*components*test*'),
            $totalViews - 8,
        ],
        [
            ['--name-not' => '*test'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*test'),
            $totalViews - 1,
        ],
        [
            ['--name-not' => '*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*test*'),
            $totalViews - 10,
        ],
        [
            ['--name-not' => '*components*test*'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*components*test*'),
            $totalViews - 8,
        ],
        [
            ['--name-not' => '*test'],
            fn (ViewQueryInterface $query) => $query
                ->whereNameDoesntEqual('*test'),
            $totalViews - 1,
        ],
    ]);
