<?php

use Mateffy\Introspect\Query\Where\Classes\WhereClassNameEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\TestModel;

it('filters classes by name ending with text', function () {
    $where = new WhereClassNameEndsWith(['Model']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports partial suffix matching', function () {
    $where = new WhereClassNameEndsWith(['Model']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereClassNameEndsWith(['Controller'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true because TestModel doesn't end with Controller
    expect($result)->toBeTrue();
});

it('returns false for non-matching suffix', function () {
    $where = new WhereClassNameEndsWith(['Controller']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeFalse();
});

it('is case-insensitive', function () {
    $where = new WhereClassNameEndsWith(['model'], caseless: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('implements RouteWhere interface', function () {
    $where = new WhereClassNameEndsWith(['Test']);

    expect($where)->toBeInstanceOf(RouteWhere::class);
});
