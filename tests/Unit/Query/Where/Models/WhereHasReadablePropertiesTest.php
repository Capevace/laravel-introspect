<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasReadableProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with readable properties', function () {
    $where = new WhereHasReadableProperties(['name']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all readable properties', function () {
    $where = new WhereHasReadableProperties(['name', 'hidden_only'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any readable property', function () {
    $where = new WhereHasReadableProperties(['name', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without readable properties', function () {
    $where = new WhereHasReadableProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasReadableProperties(['name']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('works with TestModel readonly docblock property', function () {
    $where = new WhereHasReadableProperties(['readonly_via_docblock']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});
