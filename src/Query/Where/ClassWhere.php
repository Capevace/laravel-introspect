<?php

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\Query\Where;
use Roave\BetterReflection\Reflection\ReflectionClass;

interface ClassWhere extends Where
{
    /**
     * @param ReflectionClass $value The class to filter
     */
	public function filter(ReflectionClass $value): bool;
}
