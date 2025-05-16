<?php

namespace Mateffy\Introspect;

use Mateffy\Introspect\Query\ClassQuery;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Mateffy\Introspect\Query\ModelQuery;
use Mateffy\Introspect\Query\RouteQuery;
use Mateffy\Introspect\Query\ViewQuery;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelIntrospectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-introspect')
            ->hasConfigFile()
            ->hasViews();
    }

    public function register()
    {
        parent::register();

//        $this->app->bind(ModelQueryInterface::class, ModelQuery::class);
        $this->app->bind(ClassQueryInterface::class, ClassQuery::class);
        $this->app->bind(ViewQueryInterface::class, ViewQuery::class);
        $this->app->bind(RouteQueryInterface::class, RouteQuery::class);
    }
}
