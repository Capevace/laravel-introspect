<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\Generic\WhereTextContains;
use Mateffy\Introspect\Query\Where\RouteWhere;
use ReflectionClass;

class WhereClassNameContains implements RouteWhere
{
    use WhereTextContains;

    /**
     * @param  ReflectionClass  $value
     */
    protected function getName($value): ?string
    {
        return $value->getName();
    }
}
