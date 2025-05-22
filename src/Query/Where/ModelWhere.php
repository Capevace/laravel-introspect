<?php

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\Query\Where;
use Mateffy\Introspect\Reflection\ModelReflector;

interface ModelWhere extends Where
{
    /**
     * @param  ModelReflector  $value  The class to filter
     */
    public function filter(ModelReflector $value): bool;
}
