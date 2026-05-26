<?php

use Illuminate\Support\Facades\Artisan;

$totalCommands = null;

beforeEach(function () use (&$totalCommands) {
    if ($totalCommands === null) {
        $commands = introspect()->commands()->get();
        $totalCommands = $commands->count();
    }
});

it('can query all commands', function () use (&$totalCommands) {
    $commands = introspect()
        ->commands()
        ->get();

    expect($commands)->toHaveCount($totalCommands);
});

it('can limit commands', function () {
    $commands = introspect()
        ->commands()
        ->limit(3)
        ->get();

    expect($commands->count())->toEqual(3);
});

it('can offset commands', function () use (&$totalCommands) {
    $commands = introspect()
        ->commands()
        ->offset(2)
        ->get();

    expect($commands->count())->toEqual($totalCommands - 2);
});

it('can query commands by name contains', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereNameContains($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['introspect', 2],
        ['route', function () {
            return (int) introspect()->commands()->whereNameContains('route')->get()->count();
        }],
    ]);

it('can query commands by name starts with', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereNameStartsWith($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['introspect', 2],
        ['route', function () {
            return (int) introspect()->commands()->whereNameStartsWith('route')->get()->count();
        }],
    ]);

it('can query commands by name ends with', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereNameEndsWith($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        [':views', 1],
        ['introspect', 1],
    ]);

it('can query commands by name equals', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereNameEquals($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['introspect', 1],
        ['introspect:views', 1],
        ['help', 1],
        ['non-existent-command', 0],
    ]);

it('can query commands by name does not contain', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereNameDoesntContain($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['introspect', function () {
            $total = introspect()->commands()->get()->count();
            return $total - 2; // 2 commands contain 'introspect'
        }],
    ]);

it('can query commands by signature contains', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereSignatureContains($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['--name', function () {
            return (int) introspect()->commands()->whereSignatureContains('--name')->get()->count();
        }],
    ]);

it('can query commands by description contains', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereDescriptionContains($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['interactively', 1],
        ['Query the views', 1],
    ]);

it('can query commands by description does not contain', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereDescriptionDoesntContain($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['interactively', function () {
            $total = introspect()->commands()->get()->count();
            return $total - 1;
        }],
    ]);

it('can query commands by signature does not contain', function (string $text, int $count) {
    $commands = introspect()
        ->commands()
        ->whereSignatureDoesntContain($text)
        ->get();

    expect($commands)->toHaveCount($count);
})
    ->with([
        ['--name', function () {
            $total = introspect()->commands()->get()->count();
            return $total - (int) introspect()->commands()->whereSignatureContains('--name')->get()->count();
        }],
    ]);
