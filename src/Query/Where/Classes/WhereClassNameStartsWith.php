<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextStartsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionClass;

class WhereClassNameStartsWith implements RouteWhere
{
    use WhereTextStartsWith;

    /**
     * @param  ReflectionClass|ModelReflector  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
