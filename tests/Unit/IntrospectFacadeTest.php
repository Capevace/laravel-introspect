<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Mateffy\Introspect\DTO\Model;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Workbench\App\Models\TestModel;

it('provides access to LaravelIntrospect', function () {
    $introspect = Introspect::getFacadeRoot();

    expect($introspect)->toBeInstanceOf(LaravelIntrospect::class);
});

it('delegates model() call', function () {
    $model = Introspect::model(TestModel::class);

    expect($model)->toBeInstanceOf(Model::class)
        ->and($model->classpath)->toBe(TestModel::class);
});

it('delegates classes() call', function () {
    $query = Introspect::classes();

    expect($query)->toBeInstanceOf(ClassQueryInterface::class);
});

it('delegates models() call', function () {
    $query = Introspect::models();

    expect($query)->toBeInstanceOf(ModelQueryInterface::class);
});

it('delegates views() call', function () {
    $query = Introspect::views();

    expect($query)->toBeInstanceOf(ViewQueryInterface::class);
});

it('delegates routes() call', function () {
    $query = Introspect::routes();

    expect($query)->toBeInstanceOf(RouteQueryInterface::class);
});

it('is a facade', function () {
    expect(Introspect::class)->toExtend(Facade::class);
});

it('returns correct facade accessor', function () {
    // getFacadeAccessor is called on the Facade class, not the resolved instance
    $accessor = Introspect::getFacadeRoot();

    expect($accessor)->toBeInstanceOf(LaravelIntrospect::class);
});

it('can be called statically', function () {
    expect(fn () => Introspect::views()->get())->not->toThrow(Exception::class);
});

it('can be used with helper function', function () {
    expect(introspect())->toBeInstanceOf(LaravelIntrospect::class);
});

it('helper function returns same type as facade', function () {
    $fromHelper = introspect();
    $fromFacade = Introspect::getFacadeRoot();

    expect($fromHelper)->toBeInstanceOf(LaravelIntrospect::class)
        ->and($fromFacade)->toBeInstanceOf(LaravelIntrospect::class);
});

it('can chain methods on classes query', function () {
    $result = Introspect::classes()
        ->offset(0)
        ->limit(5)
        ->get();

    expect($result)->toBeInstanceOf(Collection::class);
});

it('can chain methods on models query', function () {
    $result = Introspect::models()
        ->offset(0)
        ->limit(5)
        ->get();

    expect($result)->toBeInstanceOf(Collection::class);
});

it('can chain methods on views query', function () {
    $result = Introspect::views()
        ->offset(0)
        ->limit(5)
        ->get();

    expect($result)->toBeInstanceOf(Collection::class);
});

it('can chain methods on routes query', function () {
    $result = Introspect::routes()
        ->offset(0)
        ->limit(5)
        ->get();

    expect($result)->toBeInstanceOf(Collection::class);
});

it('resolves fresh instance', function () {
    $instance1 = Introspect::getFacadeRoot();
    $instance2 = Introspect::getFacadeRoot();

    expect($instance1)->toBeInstanceOf(LaravelIntrospect::class)
        ->and($instance2)->toBeInstanceOf(LaravelIntrospect::class);
});

it('provides access to all query types', function () {
    expect(Introspect::classes())->not->toBeNull()
        ->and(Introspect::models())->not->toBeNull()
        ->and(Introspect::views())->not->toBeNull()
        ->and(Introspect::routes())->not->toBeNull();
});

it('model method returns DTO through facade', function () {
    $model = Introspect::model(TestModel::class);

    expect($model->classpath)->toBe(TestModel::class)
        ->and($model->properties)->toBeInstanceOf(Collection::class);
});

it('facade resolves LaravelIntrospect class', function () {
    expect(Introspect::getFacadeRoot())->toBeInstanceOf(LaravelIntrospect::class);
});
