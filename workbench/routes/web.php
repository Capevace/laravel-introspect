<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/test/{param1}/what/{param2}', [\Workbench\App\Controllers\TestController::class, 'test'])
    ->name('test');

Route::post('/test/single-action/{param2}/okay/{param3}', \Workbench\App\Controllers\SingleActionTestController::class)
    ->name('test.single-action');
