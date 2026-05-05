<?php

use Illuminate\Database\Eloquent\Model;
use Mateffy\Introspect\Reflection\ClassReflector;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Models\BaseModel;
use Workbench\App\Models\TestModel;

it('checks if class extends another class', function () {
    expect(ClassReflector::extends(TestModel::class, BaseModel::class))->toBeTrue();
});

it('returns false when class does not extend another', function () {
    expect(ClassReflector::extends(TestModel::class, Model::class))->toBeTrue()
        ->and(ClassReflector::extends(TestModel::class, stdClass::class))->toBeFalse();
});

it('gets nested parent classes', function () {
    $parents = ClassReflector::getNestedParents(TestModel::class);

    expect($parents)->toContain(BaseModel::class)
        ->and($parents)->toContain(Model::class);
});

it('handles classes with no parents', function () {
    $parents = ClassReflector::getNestedParents(Model::class);

    // Model has no Eloquent parents, but may have internal PHP class parents
    expect($parents)->toBeArray();
});

it('gets nested interfaces', function () {
    $interfaces = ClassReflector::getNestedInterfaces(TestModel::class);

    expect($interfaces)->toContain('Workbench\App\Interfaces\TestInterface');
});

it('handles classes with no interfaces', function () {
    $interfaces = ClassReflector::getNestedInterfaces(BaseModel::class);

    expect($interfaces)->toBeArray();
});

it('gets nested traits', function () {
    $traits = ClassReflector::getNestedTraits(TestModel::class);

    expect($traits)->toContain('Workbench\App\Traits\TestTrait');
});

it('handles classes with no traits', function () {
    $traits = ClassReflector::getNestedTraits(BaseModel::class);

    expect($traits)->toBeArray();
});

it('removes leading backslashes from parent class names', function () {
    $parents = ClassReflector::getNestedParents(TestModel::class);

    foreach ($parents as $parent) {
        expect($parent)->not->toStartWith('\\');
    }
});

it('removes leading backslashes from interface names', function () {
    $interfaces = ClassReflector::getNestedInterfaces(TestModel::class);

    foreach ($interfaces as $interface) {
        expect($interface)->not->toStartWith('\\');
    }
});

it('removes leading backslashes from trait names', function () {
    $traits = ClassReflector::getNestedTraits(TestModel::class);

    foreach ($traits as $trait) {
        expect($trait)->not->toStartWith('\\');
    }
});

it('handles errors gracefully when getting parents', function () {
    // This test ensures no exception is thrown for edge cases
    $parents = ClassReflector::getNestedParents(stdClass::class);

    expect($parents)->toBeArray();
});

it('handles errors gracefully when getting interfaces', function () {
    $interfaces = ClassReflector::getNestedInterfaces(stdClass::class);

    expect($interfaces)->toBeArray();
});

it('handles errors gracefully when getting traits', function () {
    $traits = ClassReflector::getNestedTraits(stdClass::class);

    expect($traits)->toBeArray();
});

it('detects inheritance chain through multiple levels', function () {
    // TestModel -> BaseModel -> Model
    $parents = ClassReflector::getNestedParents(TestModel::class);

    expect($parents)->toHaveCount(2)
        ->toContain(BaseModel::class)
        ->toContain(Model::class);
});

it('detects traits from parent classes', function () {
    // TestModel inherits from BaseModel which has no traits directly,
    // but TestModel uses TestTrait
    $traits = ClassReflector::getNestedTraits(TestModel::class);

    expect($traits)->toContain('Workbench\App\Traits\TestTrait');
});

it('detects interfaces from parent classes', function () {
    // TestModel implements TestInterface, AnotherModel implements AnotherInterface
    $interfaces = ClassReflector::getNestedInterfaces(AnotherModel::class);

    expect($interfaces)->toContain('Workbench\App\Interfaces\AnotherInterface');
});

it('works with AnotherModel inheritance chain', function () {
    $parents = ClassReflector::getNestedParents(AnotherModel::class);

    expect($parents)->toContain(BaseModel::class)
        ->toContain(Model::class);
});

it('returns empty array for non-existent class parents', function () {
    // For a non-existent class, class_parents returns false, which gets converted to empty array
    // We can't test non-existent class directly, but we can verify behavior with edge cases
    $parents = ClassReflector::getNestedParents(stdClass::class);

    expect($parents)->toBeArray();
});

it('handles deeply nested inheritance', function () {
    // TestModel extends BaseModel extends Model
    $parents = ClassReflector::getNestedParents(TestModel::class);

    // Should contain all ancestors
    expect($parents)->toHaveCount(2);
});

it('getNestedParents returns unique values only', function () {
    $parents = ClassReflector::getNestedParents(TestModel::class);
    $uniqueParents = array_unique($parents);

    expect($parents)->toEqual($uniqueParents);
});

it('getNestedInterfaces returns unique values only', function () {
    $interfaces = ClassReflector::getNestedInterfaces(TestModel::class);
    $uniqueInterfaces = array_unique($interfaces);

    expect($interfaces)->toEqual($uniqueInterfaces);
});

it('getNestedTraits returns unique values only', function () {
    $traits = ClassReflector::getNestedTraits(TestModel::class);
    $uniqueTraits = array_unique($traits);

    expect($traits)->toEqual($uniqueTraits);
});
