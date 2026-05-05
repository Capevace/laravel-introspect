<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasWritableProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with writable properties', function () {
    $where = new WhereHasWritableProperties(['name']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all writable properties', function () {
    $where = new WhereHasWritableProperties(['name', 'appended_only'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any writable property', function () {
    $where = new WhereHasWritableProperties(['name', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without writable properties', function () {
    $where = new WhereHasWritableProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasWritableProperties(['name']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('works with TestModel writeonly docblock property', function () {
    $where = new WhereHasWritableProperties(['writeonly_via_docblock']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});
