<?php

namespace Mateffy\Introspect\Facades;

use Illuminate\Support\Facades\Facade;
use Mateffy\Introspect\LaravelIntrospect;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Reflection\ModelReflector;

/**
 * @see LaravelIntrospect
 *
 * @method static ModelReflector model(string $class)
 * @method static ClassQueryInterface classes()
 * @method static ModelQueryInterface models()
 * @method static ViewQueryInterface views()
 * @method static RouteQueryInterface routes()
 */
class Introspect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelIntrospect::class;
    }

    public static function codebase(string $path, array $directories = LaravelIntrospect::DEFAULT_DIRECTORIES): LaravelIntrospect
    {
        return app(LaravelIntrospect::class, ['path' => $path, 'directories' => $directories]);
    }
}
