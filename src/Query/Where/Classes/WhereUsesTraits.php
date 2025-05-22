<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Reflection\ModelReflector;
use Mateffy\Introspect\Support\RegexHelper;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereUsesTraits implements ClassWhere
{
    use NotInverter;

    /**
     * @var Collection<class-string>
     */
    public Collection $traits;

    public function __construct(array|Collection $traits, public bool $not = false, public bool $all = true)
    {
        $this->traits = collect($traits);
    }

    public function filter(ReflectionClass|ModelReflector $value): bool
    {
        $traits = class_uses_recursive($value->getName());

        if ($this->all) {
            $passes = collect($this->traits)
                ->every(fn (string $trait) => RegexHelper::matches($trait, $traits));
        } else {
            $passes = collect($this->traits)
                ->some(fn (string $trait) => RegexHelper::matches($trait, $traits));
        }

        return $this->invert($passes, $this->not);
    }
}
