<?php

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

it('can query all views', function () {
    Introspect::views()
        ->whereOr(fn ($query) => $query
            ->whereImplements(ClassA::class)
            ->whereImplements(ClassB::class)
            ->where(``)
        )
        ->whereExtends(ClassC::class)

    Introspect::models()

});
