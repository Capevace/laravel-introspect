<?php

use Mateffy\Introspect\Query\Where\Views\WhereViewNameContains;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views by name containing text', function () {
    $where = new WhereViewNameContains(['test']);

    // TestModel should match
    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereViewNameContains(['*test-welcome']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereViewNameContains(['nonexistent'], not: true);

    $result = $where->filter('workbench::test-welcome');

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereViewNameContains(['test']);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('is case-insensitive', function () {
    $where = new WhereViewNameContains(['TEST'], caseless: true);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('returns false for non-matching views', function () {
    $where = new WhereViewNameContains(['nonexistent']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeFalse();
});
