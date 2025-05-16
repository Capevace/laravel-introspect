<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereUsesTrait implements ClassWhere
{
    use NotInverter;

	public function __construct(public string $trait, public bool $not = false)
	{
	}

	public function filter(ReflectionClass $value): bool
	{
		return $this->invert(in_array($this->trait, $value->getTraitNames()), $this->not);
	}
}
