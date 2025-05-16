<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereImplementsInterface implements ClassWhere
{
    use NotInverter;

	public function __construct(public string $interface, public bool $not = false)
	{
	}

	public function filter(ReflectionClass $value): bool
	{
		return $this->invert($value->implementsInterface($this->interface), $this->not);
	}
}
