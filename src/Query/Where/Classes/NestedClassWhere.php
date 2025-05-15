<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Builder\WhereClasses;
use Mateffy\Introspect\Query\Builder\WithPagination;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Where\NestedWhere;

class NestedClassWhere extends NestedWhere implements ClassQueryInterface
{
    use WhereClasses;
}
