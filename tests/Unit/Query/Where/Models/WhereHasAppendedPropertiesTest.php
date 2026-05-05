<?php

use Mateffy\Introspect\Query\Where\Models\WhereHasAppendedProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models with appended properties', function () {
    $where = new WhereHasAppendedProperties(['appended_only']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with all appended properties', function () {
    // AnotherModel has appended_only and name in appends
    $where = new WhereHasAppendedProperties(['appended_only', 'name'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models with any appended property', function () {
    $where = new WhereHasAppendedProperties(['appended_only', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models without appended properties', function () {
    // TestModel doesn't have specific appended properties
    $where = new WhereHasAppendedProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasAppendedProperties(['test']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('returns false for models without matching appended properties', function () {
    $where = new WhereHasAppendedProperties(['nonexistent']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeFalse();
});
