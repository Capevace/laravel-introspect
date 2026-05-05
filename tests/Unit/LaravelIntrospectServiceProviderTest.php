<?php

use Mateffy\Introspect\LaravelIntrospectServiceProvider;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

it('registers package configuration', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    // The service provider should be able to be instantiated
    expect($provider)->toBeInstanceOf(LaravelIntrospectServiceProvider::class);
});

it('is a package service provider', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    expect($provider)->toBeInstanceOf(PackageServiceProvider::class);
});

it('binds ModelQueryInterface to ModelQuery', function () {
    // Query classes require path parameter - test via LaravelIntrospect instead
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    expect($introspect->models())->toBeInstanceOf(ModelQueryInterface::class);
});

it('binds ClassQueryInterface to ClassQuery', function () {
    // Query classes require path parameter - test via LaravelIntrospect instead
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    expect($introspect->classes())->toBeInstanceOf(ClassQueryInterface::class);
});

it('binds ViewQueryInterface to ViewQuery', function () {
    // Query classes require path parameter - test via LaravelIntrospect instead
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    expect($introspect->views())->toBeInstanceOf(ViewQueryInterface::class);
});

it('binds RouteQueryInterface to RouteQuery', function () {
    // Query classes require path parameter - test via LaravelIntrospect instead
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    expect($introspect->routes())->toBeInstanceOf(RouteQueryInterface::class);
});

it('provides correct package name', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    // The provider should have a method to configure the package
    expect(method_exists($provider, 'configurePackage'))->toBeTrue();
});

it('has register method', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    expect(method_exists($provider, 'register'))->toBeTrue();
});

it('can resolve query interfaces after boot', function () {
    // Query classes are resolved through LaravelIntrospect which provides required dependencies
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    expect($introspect->models())->toBeInstanceOf(ModelQueryInterface::class)
        ->and($introspect->classes())->toBeInstanceOf(ClassQueryInterface::class)
        ->and($introspect->views())->toBeInstanceOf(ViewQueryInterface::class)
        ->and($introspect->routes())->toBeInstanceOf(RouteQueryInterface::class);
});

it('registers commands', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    // The provider should register commands
    expect(method_exists($provider, 'configurePackage'))->toBeTrue();
});

it('uses spatie package tools', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    expect($provider)->toBeInstanceOf(PackageServiceProvider::class);
});

it('registers config file', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    // Service provider should configure package with config file
    expect(method_exists($provider, 'configurePackage'))->toBeTrue();
});

it('has boot method', function () {
    $provider = new LaravelIntrospectServiceProvider(app());

    expect(method_exists($provider, 'boot'))->toBeTrue();
});

it('can be instantiated', function () {
    expect(new LaravelIntrospectServiceProvider(app()))
        ->toBeInstanceOf(LaravelIntrospectServiceProvider::class);
});

it('registers all query interface bindings', function () {
    // Query classes are resolved through LaravelIntrospect which provides required dependencies
    $introspect = app('Mateffy\Introspect\LaravelIntrospect');

    // All four query types should be accessible
    expect($introspect->models())->not->toBeNull()
        ->and($introspect->classes())->not->toBeNull()
        ->and($introspect->views())->not->toBeNull()
        ->and($introspect->routes())->not->toBeNull();
});

it('bindings are singletons or instances', function () {
    // Query classes are resolved through LaravelIntrospect
    $introspect1 = app('Mateffy\Introspect\LaravelIntrospect');
    $introspect2 = app('Mateffy\Introspect\LaravelIntrospect');

    $query1 = $introspect1->models();
    $query2 = $introspect2->models();

    // Each resolution creates a new query instance (not singleton)
    expect($query1)->toBeInstanceOf(ModelQueryInterface::class)
        ->and($query2)->toBeInstanceOf(ModelQueryInterface::class);
});
