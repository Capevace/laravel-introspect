<?php

namespace Mateffy\Introspect\Query\Where\Classes;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\ClassWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class WhereImplementsInterfaces implements ClassWhere
{
    use NotInverter;

    /**
     * @param  class-string[]|Collection<class-string>  $interfaces
     */
    public Collection $interfaces;

    public function __construct(array|Collection $interfaces, public bool $not = false, public bool $all = true)
    {
        $this->interfaces = collect($interfaces);
    }

    public function filter(ReflectionClass $value): bool
    {
        // Note: we could use $value->implementsInterface() here, but for some reason its ultra slow,
        // taking around 4s per class (I think because it reflects the interfaces too, but idk).
        // Using class_implements is much faster, taking around 0.1s per class.

        if ($this->all) {
            $passes = collect($this->interfaces)
                ->every(fn (string $interface) => in_array($interface, class_implements($value->getName())));
        } else {
            $passes = collect($this->interfaces)
                ->some(fn (string $interface) => in_array($interface, class_implements($value->getName())));
        }

        return $this->invert($passes, $this->not);
    }
}
