<?php

namespace Mateffy\Introspect;

use Illuminate\Container\Container;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Reflection\ModelReflector;

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

    public function model(string $class): ModelReflector
    {
        return Container::getInstance()->make(ModelReflector::class, ['model' => $class]);
    }

    public function classes(): ClassQueryInterface&QueryPerformerInterface
    {
        return Container::getInstance()->make(ClassQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function models(): ModelQueryInterface&QueryPerformerInterface
    {
        return Container::getInstance()->make(ModelQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function views(): ViewQueryInterface&QueryPerformerInterface
    {
        return Container::getInstance()->make(ViewQueryInterface::class, ['path' => $this->path, 'directories' => $this->directories]);
    }

    public function routes(): RouteQueryInterface&QueryPerformerInterface
    {
        return Container::getInstance()->make(RouteQueryInterface::class, ['path' => $this->path]);
    }
}
