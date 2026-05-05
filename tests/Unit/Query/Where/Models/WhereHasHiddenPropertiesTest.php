<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasHiddenProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with hidden properties', function () {
    $where = new WhereHasHiddenProperties(['hidden_only']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all hidden properties', function () {
    // AnotherModel has hidden_only in hidden
    $where = new WhereHasHiddenProperties(['hidden_only'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any hidden property', function () {
    $where = new WhereHasHiddenProperties(['hidden_only', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without hidden properties', function () {
    $where = new WhereHasHiddenProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasHiddenProperties(['password']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('works with TestModel', function () {
    // TestModel doesn't have hidden properties directly
    $where = new WhereHasHiddenProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});
