<?php

namespace Mateffy\Introspect;

use Illuminate\Container\Container;
use Mateffy\Introspect\DTO\Model;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Reflection\ModelReflector;
use ReflectionException;

class LaravelIntrospect
{
    public const DEFAULT_DIRECTORIES = ['app'];

    protected string $path;

    public function __construct(
        ?string $path = null,
        protected array $directories = self::DEFAULT_DIRECTORIES,
    ) {
        $this->path = $path ?? app()->basePath();
    }

    /**
     * @throws ReflectionException
     */
    public function model(string $class): Model
    {
        return ModelReflector::makeFromClasspath($class)->dto();
    }

    public function classes(): ClassQueryInterface&QueryPerformerInterface&PaginationInterface
    {
        return Container::getInstance()->make(ClassQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function models(): ModelQueryInterface&QueryPerformerInterface&PaginationInterface
    {
        return Container::getInstance()->make(ModelQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function views(): ViewQueryInterface&QueryPerformerInterface&PaginationInterface
    {
        return Container::getInstance()->make(ViewQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function routes(): RouteQueryInterface&QueryPerformerInterface&PaginationInterface
    {
        return Container::getInstance()->make(RouteQueryInterface::class, ['path' => $this->path]);
    }
}
