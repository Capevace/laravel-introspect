<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextEquals;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionClass;

class WhereClassNameEquals implements RouteWhere
{
    use WhereTextEquals;

    /**
     * @param  ReflectionClass|ModelReflector  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
