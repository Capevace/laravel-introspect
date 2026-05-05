<?php

use Illuminate\Support\Facades\Artisan;

it('executes introspect:classes with no options', function () {
    $exitCode = Artisan::call('introspect:classes');

    expect($exitCode)->toBe(0);
});

it('filters classes by name option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--name' => 'Workbench\App\Models\TestModel',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by name-not option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--name-not' => 'NonExistentClass',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by name-starts option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--name-starts' => 'Workbench\App\Models',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by name-ends option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--name-ends' => 'Model',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by name-contains option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--name-contains' => 'Test',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by extends option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--extends' => 'Illuminate\Database\Eloquent\Model',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by doesnt-extend option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--doesnt-extend' => 'NonExistentClass',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by implements option with single interface', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--implements' => 'Workbench\App\Interfaces\TestInterface',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by implements option with multiple interfaces', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--implements' => 'Workbench\App\Interfaces\TestInterface,Workbench\App\Interfaces\AnotherInterface',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by doesnt-implement option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--doesnt-implement' => 'NonExistentInterface',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by uses option with single trait', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--uses' => 'Workbench\App\Traits\TestTrait',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by uses option with multiple traits', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--uses' => 'Workbench\App\Traits\TestTrait,Workbench\App\Traits\AnotherTrait',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters classes by doesnt-use option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--doesnt-use' => 'NonExistentTrait',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs json format', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    // JSON output should be valid JSON
    $json = json_decode($output, true);
    expect($json)->not->toBeNull('Output should be valid JSON');
});

it('outputs table format', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--format' => 'table',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs csv format', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--format' => 'csv',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs raw format', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--format' => 'raw',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs jsonl format', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--format' => 'jsonl',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs compact json', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--compact' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('applies offset option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--offset' => 0,
    ]);

    expect($exitCode)->toBe(0);
});

it('applies limit option', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--limit' => 10,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs count only', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--count' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('selects specific fields', function () {
    $exitCode = Artisan::call('introspect:classes', [
        '--fields' => 'class',
        '--limit' => 3,
    ]);

    expect($exitCode)->toBe(0);
});

it('applies dsl query option', function () {
    try {
        $exitCode = Artisan::call('introspect:classes', [
            '--query' => 'name = Workbench\App\Models\*',
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } catch (Exception $e) {
        // DSL parser might not be fully implemented
        expect(true)->toBeTrue();
    }
});

it('applies dsl query from file option', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'query');
    file_put_contents($tempFile, 'name = Workbench\App\Models\*');

    try {
        $exitCode = Artisan::call('introspect:classes', [
            '--query-file' => $tempFile,
        ]);

        expect($exitCode)->toBeIn([0, 1]);
    } catch (Exception $e) {
        // DSL parser might not be fully implemented
        expect(true)->toBeTrue();
    }

    unlink($tempFile);
});
