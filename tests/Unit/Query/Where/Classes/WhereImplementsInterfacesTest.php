<?php

use Mateffy\Introspect\Query\Where\Classes\WhereImplementsInterfaces;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Interfaces\AnotherInterface;
use Workbench\App\Interfaces\TestInterface;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\TestModel;

it('filters classes implementing a specific interface', function () {
    $where = new WhereImplementsInterfaces([TestInterface::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes implementing all specified interfaces', function () {
    // TestModel only implements TestInterface
    $where = new WhereImplementsInterfaces([TestInterface::class], all: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes implementing any specified interface', function () {
    $where = new WhereImplementsInterfaces([TestInterface::class, 'OtherInterface'], all: false);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereImplementsInterfaces(['Workbench\App\Interfaces\*']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereImplementsInterfaces(['NonExistentInterface'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('works with ModelReflector', function () {
    $where = new WhereImplementsInterfaces([TestInterface::class]);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('implements ClassWhere interface', function () {
    $where = new WhereImplementsInterfaces(['TestInterface']);

    expect($where)->toBeInstanceOf(ClassWhere::class);
});

it('works with AnotherModel', function () {
    $where = new WhereImplementsInterfaces([AnotherInterface::class]);

    $reflection = ReflectionClass::createFromName(AnotherModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});
