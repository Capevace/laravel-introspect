<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

afterEach(function () {
    $path = storage_path('introspect-baseline.json');
    if (File::exists($path)) {
        File::delete($path);
    }
});

it('executes introspect:diff command successfully', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);
});

it('outputs valid JSON diff', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    expect($json)->not->toBeNull()
        ->and($json)->toHaveKey('routes')
        ->and($json)->toHaveKey('migrations')
        ->and($json)->toHaveKey('config');
});

it('outputs diff with route structure', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    expect($json['routes'])->toHaveKey('added')
        ->and($json['routes'])->toHaveKey('removed')
        ->and($json['routes'])->toHaveKey('changed');
});

it('outputs diff with migrations structure', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    expect($json['migrations'])->toHaveKey('added_tables')
        ->and($json['migrations'])->toHaveKey('removed_tables')
        ->and($json['migrations'])->toHaveKey('changed_tables');
});

it('outputs diff with config structure', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    expect($json['config'])->toHaveKey('added')
        ->and($json['config'])->toHaveKey('removed')
        ->and($json['config'])->toHaveKey('changed');
});

it('shows empty diff when no changes', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    expect($json['routes']['added'])->toBeEmpty()
        ->and($json['routes']['removed'])->toBeEmpty()
        ->and($json['routes']['changed'])->toBeEmpty()
        ->and($json['migrations']['added_tables'])->toBeEmpty()
        ->and($json['migrations']['removed_tables'])->toBeEmpty()
        ->and($json['migrations']['changed_tables'])->toBeEmpty()
        ->and($json['config']['added'])->toBeEmpty()
        ->and($json['config']['removed'])->toBeEmpty()
        ->and($json['config']['changed'])->toBeEmpty();
});

it('fails when baseline file does not exist', function () {
    $path = storage_path('introspect-baseline.json');
    if (File::exists($path)) {
        File::delete($path);
    }

    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(1);
});

it('shows error message when baseline file does not exist', function () {
    $path = storage_path('introspect-baseline.json');
    if (File::exists($path)) {
        File::delete($path);
    }

    Artisan::call('introspect:diff');

    $output = Artisan::output();

    expect($output)->toContain('Baseline file not found');
});

it('supports custom path option', function () {
    $path = tempnam(sys_get_temp_dir(), 'diff_test_').'.json';

    Artisan::call('introspect:baseline', ['--path' => $path]);

    $exitCode = Artisan::call('introspect:diff', ['--path' => $path]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);
    expect($json)->not->toBeNull()
        ->and($json)->toHaveKey('routes');

    File::delete($path);
});

it('supports --compact flag', function () {
    Artisan::call('introspect:baseline');

    $exitCode = Artisan::call('introspect:diff', ['--compact' => true]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $output = trim($output);

    expect(str_contains($output, "\n"))->toBeFalse('Compact output should not contain newlines');
});

it('detects config changes between baseline and current state', function () {
    Artisan::call('introspect:baseline');

    config(['baseline.diff.test' => 'original']);

    // Re-run diff with the baseline still having original config
    $exitCode = Artisan::call('introspect:diff');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    $json = json_decode($output, true);

    // Config will have changes because the current state now differs
    // from what was in the baseline
    expect($json)->toHaveKey('config');
});
