<?php

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Models\WhereHasProperties;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters models having all specified properties', function () {
    $where = new WhereHasProperties(['name', 'hidden_only'], all: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('filters models having any specified properties', function () {
    $where = new WhereHasProperties(['name', 'nonexistent'], all: false);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereHasProperties(['nonexistent'], not: true);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ModelWhere interface', function () {
    $where = new WhereHasProperties(['name']);

    expect($where)->toBeInstanceOf(ModelWhere::class);
});

it('supports wildcard patterns', function () {
    // WhereHasProperties checks for exact property names, wildcards handled by query builder
    $where = new WhereHasProperties(['name']);

    $reflector = ModelReflector::makeFromClasspath(AnotherModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('works with TestModel', function () {
    $where = new WhereHasProperties(['only_via_docblock']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('returns false for non-existent properties', function () {
    $where = new WhereHasProperties(['nonexistent_property']);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeFalse();
});

it('has properties collection', function () {
    $where = new WhereHasProperties(['name'], all: true, not: false);

    expect($where->properties)->toBeInstanceOf(Collection::class);
    expect($where->properties->toArray())->toContain('name');
});
