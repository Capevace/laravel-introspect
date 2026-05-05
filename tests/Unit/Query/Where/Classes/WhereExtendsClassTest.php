<?php

use Illuminate\Database\Eloquent\Model;
use Mateffy\Introspect\Query\Where\Classes\WhereExtendsClass;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Workbench\App\Models\BaseModel;
use Workbench\App\Models\TestModel;

it('filters classes extending a specific parent', function () {
    $where = new WhereExtendsClass([BaseModel::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes extending all specified parents', function () {
    $where = new WhereExtendsClass([BaseModel::class, Model::class], all: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('filters classes extending any specified parent', function () {
    $where = new WhereExtendsClass([BaseModel::class, 'SomeOtherClass'], all: false);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('supports wildcard patterns in parent names', function () {
    $where = new WhereExtendsClass(['Workbench\App\Models\*']);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereExtendsClass(['NonExistentClass'], not: true);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('handles deeply nested inheritance', function () {
    $where = new WhereExtendsClass([Model::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});

it('works with ModelReflector', function () {
    $where = new WhereExtendsClass([BaseModel::class]);

    $reflector = ModelReflector::makeFromClasspath(TestModel::class);
    $result = $where->filter($reflector);

    expect($result)->toBeTrue();
});

it('implements ClassWhere interface', function () {
    $where = new WhereExtendsClass(['Model']);

    expect($where)->toBeInstanceOf(ClassWhere::class);
});

it('normalizes class names with leading backslashes', function () {
    $where = new WhereExtendsClass(['\\'.BaseModel::class]);

    $reflection = ReflectionClass::createFromName(TestModel::class);
    $result = $where->filter($reflection);

    expect($result)->toBeTrue();
});
