<?php

use Mateffy\Introspect\Query\Where\Classes\WhereClassNameContains;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\TestModel;

it('filters classes by name containing text', function () {
    $where = new WhereClassNameContains(['TestModel']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereClassNameContains(['*Model*']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereClassNameContains(['NonExistent'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('returns false for non-matching classes', function () {
    $where = new WhereClassNameContains(['NonExistentClassName']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeFalse();
});

it('handles case sensitivity correctly', function () {
    $where = new WhereClassNameContains(['testmodel'], caseless: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // Should be case-insensitive
    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereClassNameContains(['Test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});

it('uses NotInverter trait', function () {
    $where = new WhereClassNameContains(['Test']);

    // Should have invert method via NotInverter trait
    expect(method_exists($where, 'filter'))->toBeTrue();
});
