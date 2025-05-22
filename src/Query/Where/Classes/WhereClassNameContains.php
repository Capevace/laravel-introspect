<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextContains;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionClass;

class WhereClassNameContains implements RouteWhere
{
    use WhereTextContains;

    /**
     * @param  ReflectionClass|ModelReflector  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
