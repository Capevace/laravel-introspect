<?php

use Mateffy\Introspect\Query\Where\Views\WhereViewNameStartsWith;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views by name starting with text', function () {
    $where = new WhereViewNameStartsWith(['workbench::']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('supports partial prefix matching', function () {
    $where = new WhereViewNameStartsWith(['workbench::test-']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereViewNameStartsWith(['nonexistent'], not: true);

    $result = $where->filter('workbench::test-welcome');

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereViewNameStartsWith(['test']);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('is case-insensitive', function () {
    $where = new WhereViewNameStartsWith(['WORKBENCH::'], caseless: true);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('returns false for non-matching prefix', function () {
    $where = new WhereViewNameStartsWith(['other']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeFalse();
});
