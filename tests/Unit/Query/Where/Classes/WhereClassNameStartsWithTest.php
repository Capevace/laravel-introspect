<?php

use Mateffy\Introspect\Query\Where\Classes\WhereClassNameStartsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\TestModel;

it('filters classes by name starting with text', function () {
    $where = new WhereClassNameStartsWith(['Workbench']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports partial prefix matching', function () {
    $where = new WhereClassNameStartsWith(['Workbench\\App']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereClassNameStartsWith(['Other'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('returns false for non-matching prefix', function () {
    $where = new WhereClassNameStartsWith(['OtherNamespace']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $where = new WhereClassNameStartsWith(['workbench'], caseless: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereClassNameStartsWith(['Test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
