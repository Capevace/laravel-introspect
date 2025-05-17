<?php

namespace Mateffy\Introspect\Query;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Builder\WhereModels;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Reflection\ModelReflector;
use Roave\BetterReflection\Reflection\ReflectionClass;

class ModelQuery extends ClassQuery implements ModelQueryInterface, PaginationInterface, QueryPerformerInterface
{
    use WhereModels;

    public function createSubquery(): self
    {
        return new ModelQuery(path: $this->path, directories: $this->directories);
    }

    public function get(): Collection
    {
        $paths = $this->getPaths();
        $reflector = $this->makeReflector($paths);

        return $this->getAllClasses($paths, $reflector)
            ->map(fn (string $class) => $reflector->reflectClass($class))
            ->filter(fn (ReflectionClass $class) => $class->isSubclassOf(Model::class))
            ->map(fn (ReflectionClass $class) => ModelReflector::makeFromReflection($class))
            ->filter(fn (ModelReflector $class) => $this->wheres->every(fn (Where $where) => $where->filter($class)))
            ->values()
            ->map(fn (ModelReflector $class) => $class->dto());
    }
}
