<?php

use Mateffy\Introspect\Query\Where\Classes\WhereClassNameEquals;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\TestModel;

it('filters classes by exact name match', function () {
    $where = new WhereClassNameEquals([TestModel::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereClassNameEquals(['Workbench\App\Models\*']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereClassNameEquals(['NonExistentClass'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('returns false for non-matching name', function () {
    $where = new WhereClassNameEquals(['OtherClass']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $lowerClass = strtolower(TestModel::class);
    $where = new WhereClassNameEquals([$lowerClass], caseless: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereClassNameEquals(['Test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
