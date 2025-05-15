<?php

namespace Mateffy\Introspect\Reflection;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mateffy\Introspect\Reflection\ModelReflector\HasProperties;
use ReflectionException;
use Roave\BetterReflection\Reflection\ReflectionClass;

class ModelReflector implements Arrayable
{
    use HasProperties;

    public function __construct(
        protected string $model,
        protected Model $instance,
        protected ReflectionClass $reflection
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public static function makeFromClasspath(string $class): self
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} does not exist.");
        }

        if (!is_subclass_of($class, Model::class)) {
            throw new InvalidArgumentException("Class {$class} is not a subclass of " . Model::class);
        }

        $instance = new $class();

        return new self(
            model: $class,
            instance: $instance,
            reflection: ReflectionClass::createFromInstance($instance)
        );
    }

    public static function makeFromReflection(ReflectionClass $reflection): self
    {
        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("Class {$reflection->getName()} is not instantiable.");
        }
//
        if (!$reflection->isSubclassOf(Model::class)) {
            throw new InvalidArgumentException("Class {$reflection->getName()} is not a subclass of " . Model::class);
        }

        $class = $reflection->getName();
        $instance = new $class();

        return new self(
            model: $class,
            instance: $instance,
            reflection: $reflection
        );
    }

    /**
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'properties' => $this->properties()->toArray(),
        ];
    }
}
