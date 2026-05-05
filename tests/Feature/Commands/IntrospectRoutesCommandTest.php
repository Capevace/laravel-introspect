<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Workbench\App\Controllers\TestController;

beforeEach(function () {
    // Clear and set up routes for testing
    Route::getRoutes()->refreshNameLookups();
});

it('executes introspect:routes with no options', function () {
    $exitCode = Artisan::call('introspect:routes');

    expect($exitCode)->toBe(0);
});

it('filters routes by name option', function () {
    Route::get('/test-route-name', fn () => 'test')->name('test.route.name');

    $exitCode = Artisan::call('introspect:routes', [
        '--name' => 'test.route.name',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by name-not option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--name-not' => 'nonexistent.route',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by name-starts option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--name-starts' => 'test',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by name-ends option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--name-ends' => '.name',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by name-contains option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--name-contains' => 'route',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by path option', function () {
    Route::get('/custom-path-test', fn () => 'test')->name('custom.path.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--path' => 'custom-path-test',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by path-not option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--path-not' => 'nonexistent-path',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by path-starts option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--path-starts' => 'api',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by path-ends option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--path-ends' => 'test',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by path-contains option', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--path-contains' => 'user',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by controller option', function () {
    Route::get('/controller-test', [TestController::class, 'index'])
        ->name('controller.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--controller' => 'Workbench\App\Controllers\TestController',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by controller with method option', function () {
    Route::get('/controller-method-test', [TestController::class, 'show'])
        ->name('controller.method.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--controller' => 'Workbench\App\Controllers\TestController',
        '--controller-method' => 'show',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by middleware option', function () {
    Route::get('/middleware-test', fn () => 'test')
        ->name('middleware.test')
        ->middleware('web');

    $exitCode = Artisan::call('introspect:routes', [
        '--middleware' => 'web',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by middleware-all option', function () {
    Route::get('/middleware-all-test', fn () => 'test')
        ->name('middleware.all.test')
        ->middleware(['web', 'auth']);

    $exitCode = Artisan::call('introspect:routes', [
        '--middleware-all' => 'web,auth',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by middleware-any option', function () {
    Route::get('/middleware-any-test', fn () => 'test')
        ->name('middleware.any.test')
        ->middleware(['web']);

    $exitCode = Artisan::call('introspect:routes', [
        '--middleware-any' => 'web,api',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by method option', function () {
    Route::post('/method-test', fn () => 'test')->name('method.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--method' => 'POST',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by methods option', function () {
    Route::post('/methods-test', fn () => 'test')->name('methods.test');
    Route::put('/methods-test-2', fn () => 'test')->name('methods.test.2');

    $exitCode = Artisan::call('introspect:routes', [
        '--methods' => 'POST,PUT',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by has-param option', function () {
    Route::get('/param-test/{id}', fn ($id) => 'test')->name('param.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--has-param' => 'id',
    ]);

    expect($exitCode)->toBe(0);
});

it('filters routes by has-params option', function () {
    Route::get('/params-test/{id}/{slug}', fn ($id, $slug) => 'test')->name('params.test');

    $exitCode = Artisan::call('introspect:routes', [
        '--has-params' => 'id,slug',
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs json format', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    // JSON output should be valid JSON
    $json = json_decode($output, true);
    expect($json)->not->toBeNull('Output should be valid JSON');
});

it('outputs table format', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--format' => 'table',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs csv format', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--format' => 'csv',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs raw format', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--format' => 'raw',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs jsonl format', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--format' => 'jsonl',
        '--limit' => 5,
    ]);

    expect($exitCode)->toBe(0);
});

it('applies offset and limit', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--offset' => 0,
        '--limit' => 10,
    ]);

    expect($exitCode)->toBe(0);
});

it('outputs count only', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--count' => true,
    ]);

    expect($exitCode)->toBe(0);
});

it('selects specific fields', function () {
    $exitCode = Artisan::call('introspect:routes', [
        '--fields' => 'name,path',
        '--limit' => 3,
    ]);

    expect($exitCode)->toBe(0);
});
