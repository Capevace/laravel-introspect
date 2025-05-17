<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextEndsWith;
use Mateffy\Introspect\Query\Where\RouteWhere;
use ReflectionClass;

class WhereClassNameEndsWith implements RouteWhere
{
    use WhereTextEndsWith;

    /**
     * @param  ReflectionClass  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
