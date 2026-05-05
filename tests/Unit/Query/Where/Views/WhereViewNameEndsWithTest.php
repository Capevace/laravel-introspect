<?php

use Mateffy\Introspect\Query\Where\Views\WhereViewNameEndsWith;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views by name ending with text', function () {
    $where = new WhereViewNameEndsWith(['welcome']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('supports partial suffix matching', function () {
    $where = new WhereViewNameEndsWith(['test-welcome']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereViewNameEndsWith(['nonexistent'], not: true);

    $result = $where->filter('workbench::test-welcome');

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereViewNameEndsWith(['test']);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('is case-insensitive', function () {
    $where = new WhereViewNameEndsWith(['WELCOME'], caseless: true);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('returns false for non-matching suffix', function () {
    $where = new WhereViewNameEndsWith(['nonexistent']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeFalse();
});
