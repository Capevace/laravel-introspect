<?php

use Mateffy\Introspect\Query\Where\Views\WhereViewNameEquals;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views by exact name match', function () {
    $where = new WhereViewNameEquals(['workbench::test-welcome']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('supports wildcard patterns', function () {
    $where = new WhereViewNameEquals(['workbench::*']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('inverts with not flag', function () {
    $where = new WhereViewNameEquals(['nonexistent'], not: true);

    $result = $where->filter('workbench::test-welcome');

    // NOT (false) = true
    expect($result)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereViewNameEquals(['test']);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('is case-insensitive', function () {
    $where = new WhereViewNameEquals([strtoupper('workbench::test-welcome')], caseless: true);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeTrue();
});

it('returns false for non-matching name', function () {
    $where = new WhereViewNameEquals(['other']);

    $result = $where->filter('workbench::test-welcome');

    expect($result)->toBeFalse();
});
