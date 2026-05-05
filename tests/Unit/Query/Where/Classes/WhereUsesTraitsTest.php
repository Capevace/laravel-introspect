<?php

use Mateffy\Introspect\Query\Where\Classes\WhereUsesTraits;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;
use Workbench\App\Traits\AnotherTrait;
use Workbench\App\Traits\TestTrait;

it('filters classes using a specific trait', function () {
    $where = new WhereUsesTraits([TestTrait::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes using all specified traits', function () {
    // TestModel only uses TestTrait
    $where = new WhereUsesTraits([TestTrait::class], all: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes using any specified trait', function () {
    $where = new WhereUsesTraits([TestTrait::class, 'OtherTrait'], all: false);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereUsesTraits(['Workbench\App\Traits\*']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereUsesTraits(['NonExistentTrait'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('works with ModelReflector', function () {
    $where = new WhereUsesTraits([TestTrait::class]);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('implements ClassWhere interface', function () {
    $where = new WhereUsesTraits(['TestTrait']);

    expect($where)->toBeInstanceOf(ClassWhere::class);
});

it('works with AnotherModel', function () {
    $where = new WhereUsesTraits([AnotherTrait::class]);

    $reflection = ReflectionClass::createFromName(AnotherModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});
