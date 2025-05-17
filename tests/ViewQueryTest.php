<?php

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

it('can query all views', function () {
    \introspect()
        ->views()
        ->or(fn (ViewQueryInterface $query) => $query
            ->and(fn (ViewQueryInterface $query) => $query
                ->whereNotUsedBy('workbench::test-welcome')
                ->whereNameStartsWith('workbench::')
                ->whereDoesntUse('workbench::components.wtf.*')
            )
            ->and(fn (ViewQueryInterface $query) => $query
                ->whereUsedBy('workbench::test-welcome')
                ->whereUsedBy('workbench::test-welcome2')
            )
        )
        ->get()
        ->dd();
});
