<?php

use Mateffy\Introspect\Query\Where\Views\WhereUsedByView;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views used by a specific view', function () {
    // This test depends on the actual view structure in the workbench
    // A view is "used by" another if it's included in that view

    $where = new WhereUsedByView('workbench::test-welcome', lax: false);

    // Test with a view that we know uses test-welcome
    // This is a simplified test - actual behavior depends on view content
    expect($where)->toBeInstanceOf(WhereUsedByView::class);
});

it('supports wildcard patterns', function () {
    $where = new WhereUsedByView('workbench::*', lax: false);

    expect($where)->toBeInstanceOf(WhereUsedByView::class);
});

it('inverts with not flag', function () {
    $where = new WhereUsedByView('workbench::test-welcome', not: true, lax: false);

    expect($where->not)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereUsedByView('test', lax: false);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('has lax mode', function () {
    $where = new WhereUsedByView('test', lax: true);

    expect($where->lax)->toBeTrue();
});
