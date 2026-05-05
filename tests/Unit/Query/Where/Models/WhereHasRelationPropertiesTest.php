<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasRelationProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with relation properties', function () {
    $where = new WhereHasRelationProperties(['test']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all relation properties', function () {
    // AnotherModel has test relationship
    $where = new WhereHasRelationProperties(['test'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any relation property', function () {
    $where = new WhereHasRelationProperties(['test', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without relation properties', function () {
    $where = new WhereHasRelationProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasRelationProperties(['user']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('works with TestModel another relationship', function () {
    // TestModel has another() relationship from BaseModel
    $where = new WhereHasRelationProperties(['another']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});
