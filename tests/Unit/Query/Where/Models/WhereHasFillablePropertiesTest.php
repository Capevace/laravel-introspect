<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasFillableProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with fillable properties', function () {
    $where = new WhereHasFillableProperties(['name']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all fillable properties', function () {
    // AnotherModel has name and appended_only in fillable
    $where = new WhereHasFillableProperties(['name', 'appended_only'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any fillable property', function () {
    $where = new WhereHasFillableProperties(['name', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without fillable properties', function () {
    $where = new WhereHasFillableProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasFillableProperties(['name']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('works with TestModel', function () {
    // TestModel inherits fillable from BaseModel
    $where = new WhereHasFillableProperties(['nested_not_overridden']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});
