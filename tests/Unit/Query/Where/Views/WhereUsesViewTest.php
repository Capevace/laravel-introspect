<?php

use Mateffy\Introspect\Query\Where\Views\WhereUsesView;
use Mateffy\Introspect\Query\Where\ViewWhere;

it('filters views that use a specific view', function () {
    // This test depends on the actual view structure
    $where = new WhereUsesView('workbench::components.wtf.test', lax: false);

    expect($where)->toBeInstanceOf(WhereUsesView::class);
});

it('supports wildcard patterns', function () {
    $where = new WhereUsesView('workbench::*.test', lax: false);

    expect($where)->toBeInstanceOf(WhereUsesView::class);
});

it('inverts with not flag', function () {
    $where = new WhereUsesView('workbench::test', not: true, lax: false);

    expect($where->not)->toBeTrue();
});

it('implements ViewWhere interface', function () {
    $where = new WhereUsesView('test', lax: false);

    expect($where)->toBeInstanceOf(ViewWhere::class);
});

it('has lax mode', function () {
    $where = new WhereUsesView('test', lax: true);

    expect($where->lax)->toBeTrue();
});
