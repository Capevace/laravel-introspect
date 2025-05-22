<?php

namespace Mateffy\Introspect\Query\Where\Models;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\ModelProperty;
use Mateffy\Introspect\Reflection\ModelReflector;

class WhereHasRelationProperties extends WhereHasProperties
{
    /**
     * @param  Collection<string, ModelProperty>  $properties
     */
    public function check(ModelReflector $reflector, Collection $properties, string $property): bool
    {
        return $properties->get($property)?->relation ?? false;
    }
}
