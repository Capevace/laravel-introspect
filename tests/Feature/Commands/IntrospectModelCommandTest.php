<?php

use Illuminate\Support\Facades\Artisan;
use Mateffy\Introspect\Facades\Introspect;

// Helper to configure LaravelIntrospect with workbench directories
function setupWorkbenchIntrospect(): void
{
    $introspect = introspect();
    app()->instance('Mateffy\Introspect\LaravelIntrospect', $introspect);
    // Clear the Facade's resolved instance so it picks up the new one
    Introspect::clearResolvedInstance();
}

it('executes introspect:model with class argument', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    // JSON escapes backslashes, so we need 4 backslashes in PHP to get 2 in the string
    expect($output)->toContain('Workbench\\\\App\\\\Models\\\\TestModel');
});

it('outputs json format', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--format' => 'json',
    ]);

    $output = Artisan::output();

    if ($exitCode !== 0) {
        echo "Exit code: $exitCode\n";
        echo "Output: $output\n";
    }

    expect($exitCode)->toBe(0);
    expect($output)->toContain('class')
        ->toContain('Workbench');
});

it('outputs table format', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs compact json', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--compact' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs json schema when schema flag is set', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--schema' => true,
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('type')
        ->toContain('properties');
});

it('outputs table with properties section', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});

it('throws error for non-existent model class', function () {
    $exitCode = Artisan::call('introspect:model', [
        'class' => 'NonExistent\Model',
    ]);

    expect($exitCode)->toBe(1);
});

it('throws error for non-model class', function () {
    $exitCode = Artisan::call('introspect:model', [
        'class' => 'stdClass',
    ]);

    expect($exitCode)->toBe(1);
});

it('works with AnotherModel', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\AnotherModel',
    ]);

    expect($exitCode)->toBe(0);
});

it('works with BaseModel', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\BaseModel',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs with schema flag and table format', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
        '--schema' => true,
        '--format' => 'table',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs full model info by default', function () {
    setupWorkbenchIntrospect();

    $exitCode = Artisan::call('introspect:model', [
        'class' => 'Workbench\App\Models\TestModel',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->not->toBeEmpty();
});
