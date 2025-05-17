<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Database\Eloquent\Model;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;

class ModelQuery extends ClassQuery implements ModelQueryInterface, PaginationInterface, QueryPerformerInterface
{
    public function createSubquery(): self
    {
        return new ModelQuery(path: $this->path, directories: $this->directories);
    }

    public function filterUsingQuery(ReflectionClass $class): bool
    {
        if (! $class->isSubclassOf(Model::class)) {
            return false;
        }

        return parent::filterUsingQuery($class);
    }

    protected function transformResult(ReflectionClass $class): mixed
    {
        return ModelReflector::makeFromReflection($class);
    }
}
