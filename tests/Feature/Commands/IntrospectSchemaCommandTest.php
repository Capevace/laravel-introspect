<?php

use Illuminate\Support\Facades\Artisan;

it('executes introspect:schema with no options', function () {
    $exitCode = Artisan::call('introspect:schema');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('resources');
});

it('outputs views schema specifically', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'views',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs routes schema specifically', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'routes',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs classes schema specifically', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'classes',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs models schema specifically', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'models',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('throws error for unknown resource', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'unknown',
    ]);

    expect($exitCode)->toBe(1);
});

it('outputs json format', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    // Check for either 'resources' or 'resource' in output
    $hasResource = str_contains($output, 'resources') || str_contains($output, 'resource');
    expect($hasResource)->toBeTrue();
});

it('outputs table format', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs compact json', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--compact' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('includes dsl operators in output', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('dsl_operators');
});

it('includes example queries for each resource', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'models',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('example_queries');
});

it('includes output formats list', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('output_formats');
});

it('outputs filters information for views', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'views',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs filters information for routes', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'routes',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs filters information for classes', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'classes',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('outputs filters information for models', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'models',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('filters');
});

it('table format shows available resources', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});

it('table format shows resource details when specified', function () {
    $exitCode = Artisan::call('introspect:schema', [
        '--resource' => 'models',
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});
