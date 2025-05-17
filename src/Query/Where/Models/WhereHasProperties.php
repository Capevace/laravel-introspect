<?php

namespace Mateffy\Introspect\Query\Where\Models;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\ModelProperty;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\ModelWhere;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionException;

class WhereHasProperties implements ModelWhere
{
    use NotInverter;

    /**
     * @var Collection<string> $properties
     */
    public Collection $properties;

    public function __construct(array|Collection $properties, public bool $not = false, public bool $all = true)
    {
        $this->properties = collect($properties);
    }

    /**
     * @throws ReflectionException
     */
    public function filter(ModelReflector $value): bool
    {
        $modelProperties = $value->properties();

        if ($this->all) {
            $passes = $this->properties->every(fn (string $property) => $this->check(
                reflector: $value,
                properties: $modelProperties,
                property: $property
            ));
        } else {
            $passes = $this->properties->some(fn (string $property) => $this->check(
                reflector: $value,
                properties: $modelProperties,
                property: $property
            ));
        }

        return $this->invert($passes, not: $this->not);
    }

    /**
     * @param Collection<string, ModelProperty> $properties
     */
    public function check(ModelReflector $reflector, Collection $properties, string $property): bool
    {
        return $properties->has($property);
    }
}
