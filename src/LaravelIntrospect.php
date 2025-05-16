<?php

namespace Mateffy\Introspect;

use Illuminate\Container\Container;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Reflection\ModelReflector;

class LaravelIntrospect
{
    public const DEFAULT_DIRECTORIES = ['app'];

    public function __construct(
        protected string $path,
        protected array $directories = self::DEFAULT_DIRECTORIES,
    )
    {
    }

    public function model(string $class): ModelReflector
    {
        return Container::getInstance()->make(ModelReflector::class, ['model' => $class]);
    }

    public function classes(): ClassQueryInterface
    {
        return Container::getInstance()->make(ClassQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function models(): ModelQueryInterface
    {
        return Container::getInstance()->make(ModelQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function views(): ViewQueryInterface
    {
        return Container::getInstance()->make(ViewQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function routes(): RouteQueryInterface
    {
        return Container::getInstance()->make(RouteQueryInterface::class, ['path' => $this->path]);
    }
}
