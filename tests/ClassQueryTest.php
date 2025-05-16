<?php

use Mateffy\Introspect\Facades\Introspect;
use Workbench\App\Models\BaseModel;
use Workbench\App\Models\TestModel;
use Workbench\App\Models\AnotherModel;
use Workbench\App\Interfaces\TestInterface;
use Workbench\App\Interfaces\AnotherInterface;
use Workbench\App\Traits\TestTrait;
use Workbench\App\Traits\AnotherTrait;

$totalClasses = 8;

it('can query all classes', function () {
	$classes = introspect()
		->classes()
		->get();

	expect($classes->count())->toBeGreaterThan(0);
});

it('can query for classes extending another class', function (string $baseClass, int $count) {
	$classes = introspect()
		->classes()
		->whereExtends($baseClass)
		->get();

	expect($classes)->toHaveCount($count);

	if ($count > 0) {
		$reflectionClass = new ReflectionClass($classes->first());
		expect($reflectionClass->isSubclassOf($baseClass))->toBeTrue();
	}
})
	->with([
		[BaseModel::class, 2],
		[TestModel::class, 0],
	]);

it('can query for classes not extending another class', function (string $baseClass, int $count) {
	$allClassesCount = introspect()
		->classes()
		->get()
		->count();

	$classes = introspect()
		->classes()
		->whereDoesntExtend($baseClass)
		->get();

	expect($classes)->toHaveCount($count);

	if ($count > 0 && $count < $allClassesCount) {
		$reflectionClass = new ReflectionClass($classes->first());
		expect($reflectionClass->isSubclassOf($baseClass))->toBeFalse();
	}
})
	->with([
		[BaseModel::class, fn() => $totalClasses - 2],
	]);

it('can query for classes implementing an interface', function (string|array $interface, int $count) {
	$classes = introspect()
		->classes()
		->whereImplements($interface)
		->get();

	expect($classes)->toHaveCount($count);
})
	->with([
		[TestInterface::class, 1],
		[AnotherInterface::class, 1],
		[[TestInterface::class, AnotherInterface::class], 0],
	]);

it('can query for classes not implementing an interface', function (string|array $interface, int $count) {
	$allClassesCount = introspect()
		->classes()
		->get()
		->count();

	$classes = introspect()
		->classes()
		->whereDoesntImplement($interface)
		->get();

	expect($classes)->toHaveCount($count);

	if ($count > 0 && $count < $allClassesCount) {
		$reflectionClass = new ReflectionClass($classes->first());

		if (is_array($interface)) {
			foreach ($interface as $singleInterface) {
				expect($reflectionClass->implementsInterface($singleInterface))->toBeFalse();
			}
		} else {
			expect($reflectionClass->implementsInterface($interface))->toBeFalse();
		}
	}
})
	->with([
		[TestInterface::class, fn() => $totalClasses - 1],
		[AnotherInterface::class, fn() => $totalClasses - 1],
		[[TestInterface::class, AnotherInterface::class], fn() => $totalClasses],
	]);

it('can query for classes using a trait', function (string|array $trait, int $count) {
	$classes = introspect()
		->classes()
		->whereUses($trait)
		->get();

	expect($classes)->toHaveCount($count);

	if ($count > 0) {
		$reflectionClass = new ReflectionClass($classes->first());
		$traits = $reflectionClass->getTraitNames();

		if (is_array($trait)) {
			$traitFound = false;
			foreach ($trait as $singleTrait) {
				if (in_array($singleTrait, $traits)) {
					$traitFound = true;
					break;
				}
			}
			expect($traitFound)->toBeTrue();
		} else {
			expect(in_array($trait, $traits))->toBeTrue();
		}
	}
})
	->with([
		[TestTrait::class, 1],
		[AnotherTrait::class, 1],
		[[TestTrait::class, AnotherTrait::class], 0],
	]);

it('can query for classes not using a trait', function (string|array $trait, int $count) {
	$allClassesCount = introspect()
		->classes()
		->get()
		->count();

	$classes = introspect()
		->classes()
		->whereDoesntUse($trait)
		->get();

	expect($classes)->toHaveCount($count);

	if ($count > 0 && $count < $allClassesCount) {
		$reflectionClass = new ReflectionClass($classes->first());
		$traits = $reflectionClass->getTraitNames();

		if (is_array($trait)) {
			foreach ($trait as $singleTrait) {
				expect(in_array($singleTrait, $traits))->toBeFalse();
			}
		} else {
			expect(in_array($trait, $traits))->toBeFalse();
		}
	}
})
	->with([
		[TestTrait::class, fn() => $totalClasses - 1],
		[AnotherTrait::class, fn() => $totalClasses - 1],
		[[TestTrait::class, AnotherTrait::class], fn() => $totalClasses],
	]);

it('can query with complex conditions', function () {
	$classes = introspect()
		->classes()
		->whereExtends(BaseModel::class)
		->whereImplements(TestInterface::class)
		->whereUses(TestTrait::class)
		->get();

	expect($classes)->toHaveCount(1);
	expect($classes->first())->toBe(TestModel::class);
});
