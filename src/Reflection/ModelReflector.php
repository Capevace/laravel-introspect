<?php

namespace Mateffy\Introspect\Reflection;

use Illuminate\Container\Container;
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
        public readonly string $model,
        public readonly Model $instance,
        public readonly ReflectionClass $reflection
    ) {}

    public function getName(): string
    {
        return $this->model;
    }

    /**
     * @throws ReflectionException
     */
    public static function makeFromClasspath(string $class): self
    {
        if (! class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} does not exist.");
        }

        if (! is_subclass_of($class, Model::class)) {
            throw new InvalidArgumentException("Class {$class} is not a subclass of ".Model::class);
        }

        $instance = new $class;

        return Container::getInstance()->make(
            self::class,
            [
                'model' => $class,
                'instance' => $instance,
                'reflection' => ReflectionClass::createFromInstance($instance),
            ]
        );
    }

    public static function makeFromReflection(ReflectionClass $reflection): self
    {
        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("Class {$reflection->getName()} is not instantiable.");
        }

        if (! $reflection->isSubclassOf(Model::class)) {
            throw new InvalidArgumentException("Class {$reflection->getName()} is not a subclass of ".Model::class);
        }

        $class = $reflection->getName();
        $instance = new $class;

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

    /**
     * @throws ReflectionException
     */
    public function dto(): \Mateffy\Introspect\DTO\Model
    {
        $description = str($this->reflection->getDocComment() ?? '')
            ->explode("\n")
            ->filter(fn (string $line) => !str($line)
                ->trim()
                ->startsWith(['/**', '*/', '*@', '* @', '@'])
            )
            ->map(fn (string $line) => str($line)
                ->ltrim(' *')
                ->ltrim('*')
                ->trim()
            )
            ->join("\n");

        if ($description === '') {
            $description = null;
        }

        $description = trim($description ?? '');

        if (empty($description)) {
            $description = null;
        }

        return new \Mateffy\Introspect\DTO\Model(
            classpath: $this->model,
            description: $description,
            properties: $this->properties(),
        );
    }
}
