<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

afterEach(function () {
    $path = storage_path('introspect-baseline.json');
    if (File::exists($path)) {
        File::delete($path);
    }
});

it('executes introspect:baseline command successfully', function () {
    $exitCode = Artisan::call('introspect:baseline');

    expect($exitCode)->toBe(0);
});

it('writes baseline JSON file to default storage path', function () {
    $path = storage_path('introspect-baseline.json');

    $exitCode = Artisan::call('introspect:baseline');

    expect($exitCode)->toBe(0)
        ->and(File::exists($path))->toBeTrue();

    $json = json_decode(File::get($path), true);
    expect($json)->not->toBeNull()
        ->and($json)->toHaveKey('timestamp')
        ->and($json)->toHaveKey('routes')
        ->and($json)->toHaveKey('migrations')
        ->and($json)->toHaveKey('config');
});

it('writes baseline to custom path', function () {
    $path = tempnam(sys_get_temp_dir(), 'baseline_test_').'.json';

    $exitCode = Artisan::call('introspect:baseline', [
        '--path' => $path,
    ]);

    expect($exitCode)->toBe(0)
        ->and(File::exists($path))->toBeTrue();

    $json = json_decode(File::get($path), true);
    expect($json)->not->toBeNull()
        ->and($json)->toHaveKey('routes');

    File::delete($path);
});

it('writes compact JSON when --compact flag is set', function () {
    $path = storage_path('introspect-baseline.json');

    $exitCode = Artisan::call('introspect:baseline', [
        '--compact' => true,
    ]);

    expect($exitCode)->toBe(0);

    $content = File::get($path);
    expect(str_contains($content, "\n"))->toBeFalse('Compact JSON should not contain newlines');
});

it('writes pretty JSON by default', function () {
    $path = storage_path('introspect-baseline.json');

    $exitCode = Artisan::call('introspect:baseline');

    expect($exitCode)->toBe(0);

    $content = File::get($path);
    expect(str_contains($content, "\n"))->toBeTrue('Default JSON should contain newlines');
});

it('creates directory if it does not exist', function () {
    $dir = sys_get_temp_dir().'/introspect_test_'.uniqid();
    $path = $dir.'/baseline.json';

    $exitCode = Artisan::call('introspect:baseline', [
        '--path' => $path,
    ]);

    expect($exitCode)->toBe(0)
        ->and(File::exists($path))->toBeTrue();

    File::deleteDirectory($dir);
});

it('output includes summary information', function () {
    Artisan::call('introspect:baseline');

    $output = Artisan::output();

    expect($output)->toContain('Baseline written to')
        ->and($output)->toContain('Routes:')
        ->and($output)->toContain('Tables:')
        ->and($output)->toContain('Config keys:');
});

it('baseline JSON contains routes array', function () {
    Artisan::call('introspect:baseline');

    $path = storage_path('introspect-baseline.json');
    $json = json_decode(File::get($path), true);

    expect($json['routes'])->toBeArray();
});

it('baseline JSON contains migrations tables', function () {
    Artisan::call('introspect:baseline');

    $path = storage_path('introspect-baseline.json');
    $json = json_decode(File::get($path), true);

    expect($json['migrations'])->toHaveKey('tables');
});

it('baseline JSON contains config object', function () {
    Artisan::call('introspect:baseline');

    $path = storage_path('introspect-baseline.json');
    $json = json_decode(File::get($path), true);

    expect($json['config'])->toBeArray();
});
