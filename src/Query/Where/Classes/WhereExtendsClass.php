<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Reflection\ClassReflector;
use Mateffy\Introspect\Reflection\ModelReflector;
use Mateffy\Introspect\Support\RegexHelper;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereExtendsClass implements ClassWhere
{
    use NotInverter;

    /**
     * @param  class-string[]|Collection<class-string>  $extends
     */
    public Collection $extends;

    public function __construct(array|Collection $extends, public bool $not = false, public bool $all = true)
    {
        $this->extends = collect($extends)
            ->map(fn (string $class) => ltrim($class, '\\'))
            ->values();
    }

    public function filter(ReflectionClass|ModelReflector $value): bool
    {
        $parents = ClassReflector::getNestedParents($value->getName());

        if ($this->all) {
            $passes = collect($this->extends)
                ->every(fn (string $interface) => RegexHelper::matches($interface, $parents));
        } else {
            $passes = collect($this->extends)
                ->some(fn (string $interface) => RegexHelper::matches($interface, $parents));
        }

        return $this->invert($passes, $this->not);
    }
}
