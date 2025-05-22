<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionClass;

class WhereClassNameEndsWith implements RouteWhere
{
    use WhereTextEndsWith;

    /**
     * @param  ReflectionClass|ModelReflector  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
