<?php

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Model;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Workbench\App\Models\TestModel;

it('creates instance with default directories', function () {
    $introspect = new LaravelIntrospect;

    expect($introspect)->toBeInstanceOf(LaravelIntrospect::class);
});

it('creates instance with custom directories', function () {
    $introspect = new LaravelIntrospect(
        path: base_path(),
        directories: ['app', 'src']
    );

    expect($introspect)->toBeInstanceOf(LaravelIntrospect::class);
});

it('returns model DTO', function () {
    $introspect = new LaravelIntrospect;

    $model = $introspect->model(TestModel::class);

    expect($model)->toBeInstanceOf(Model::class)
        ->and($model->classpath)->toBe(TestModel::class);
});

it('returns class query', function () {
    $introspect = new LaravelIntrospect;

    $query = $introspect->classes();

    expect($query)
        ->toBeInstanceOf(ClassQueryInterface::class)
        ->toBeInstanceOf(QueryPerformerInterface::class)
        ->toBeInstanceOf(PaginationInterface::class);
});

it('returns model query', function () {
    $introspect = new LaravelIntrospect;

    $query = $introspect->models();

    expect($query)
        ->toBeInstanceOf(ModelQueryInterface::class)
        ->toBeInstanceOf(QueryPerformerInterface::class)
        ->toBeInstanceOf(PaginationInterface::class);
});

it('returns view query', function () {
    $introspect = new LaravelIntrospect;

    $query = $introspect->views();

    expect($query)
        ->toBeInstanceOf(ViewQueryInterface::class)
        ->toBeInstanceOf(QueryPerformerInterface::class)
        ->toBeInstanceOf(PaginationInterface::class);
});

it('returns route query', function () {
    $introspect = new LaravelIntrospect;

    $query = $introspect->routes();

    expect($query)
        ->toBeInstanceOf(RouteQueryInterface::class)
        ->toBeInstanceOf(QueryPerformerInterface::class)
        ->toBeInstanceOf(PaginationInterface::class);
});

it('uses default directories constant', function () {
    expect(LaravelIntrospect::DEFAULT_DIRECTORIES)->toBe(['app']);
});

it('queries use configured path', function () {
    $introspect = new LaravelIntrospect(path: base_path());

    $classesQuery = $introspect->classes();
    $modelsQuery = $introspect->models();
    $viewsQuery = $introspect->views();
    $routesQuery = $introspect->routes();

    expect($classesQuery)->toBeInstanceOf(ClassQueryInterface::class)
        ->and($modelsQuery)->toBeInstanceOf(ModelQueryInterface::class)
        ->and($viewsQuery)->toBeInstanceOf(ViewQueryInterface::class)
        ->and($routesQuery)->toBeInstanceOf(RouteQueryInterface::class);
});

it('classes query can get results', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app']
    );

    $classes = $introspect->classes()->get();

    expect($classes)->toBeInstanceOf(Collection::class);
});

it('models query can get results', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app']
    );

    $models = $introspect->models()->get();

    expect($models)->toBeInstanceOf(Collection::class);
});

it('views query can get results', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app', 'resources/views']
    );

    $views = $introspect->views()->get();

    expect($views)->toBeInstanceOf(Collection::class);
});

it('routes query can get results', function () {
    $introspect = new LaravelIntrospect(path: base_path());

    $routes = $introspect->routes()->get();

    expect($routes)->toBeInstanceOf(Collection::class);
});

it('can chain query methods on class query', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app']
    );

    $results = $introspect->classes()
        ->offset(0)
        ->limit(10)
        ->get();

    expect($results)->toBeInstanceOf(Collection::class);
});

it('can chain query methods on model query', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app']
    );

    $results = $introspect->models()
        ->offset(0)
        ->limit(10)
        ->get();

    expect($results)->toBeInstanceOf(Collection::class);
});

it('can chain query methods on view query', function () {
    $introspect = new LaravelIntrospect(
        path: __DIR__.'/../../../workbench',
        directories: ['app', 'resources/views']
    );

    $results = $introspect->views()
        ->offset(0)
        ->limit(10)
        ->get();

    expect($results)->toBeInstanceOf(Collection::class);
});

it('can chain query methods on route query', function () {
    $introspect = new LaravelIntrospect(path: base_path());

    $results = $introspect->routes()
        ->offset(0)
        ->limit(10)
        ->get();

    expect($results)->toBeInstanceOf(Collection::class);
});

it('throws exception for non-existent model', function () {
    $introspect = new LaravelIntrospect;

    expect(fn () => $introspect->model('NonExistent\Model'))
        ->toThrow(InvalidArgumentException::class);
});

it('model method returns full model DTO', function () {
    $introspect = new LaravelIntrospect;

    $model = $introspect->model(TestModel::class);

    expect($model->classpath)->toBe(TestModel::class)
        ->and($model->properties)->toBeInstanceOf(Collection::class);
});
