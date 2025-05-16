<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereUsesTraits implements ClassWhere
{
    use NotInverter;

    /**
     * @var Collection<class-string> $traits
     */
    public Collection $traits;

	public function __construct(array|Collection $traits, public bool $not = false, public bool $all = true)
    {
        $this->traits = collect($traits);
    }

	public function filter(ReflectionClass $value): bool
	{
		if ($this->all) {
            $passes = collect($this->traits)
                ->every(fn(string $trait) => in_array($trait, class_uses($value->getName())));
        } else {
            $passes = collect($this->traits)
                ->some(fn(string $trait) => in_array($trait, class_uses($value->getName())));
        }

        return $this->invert($passes, $this->not);
	}
}
