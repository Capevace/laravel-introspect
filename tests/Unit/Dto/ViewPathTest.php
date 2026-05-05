<?php

use Mateffy\Introspect\DTO\ViewPath;

it('can create a view path DTO', function () {
    $viewPath = new ViewPath(
        path: '/resources/views/welcome.blade.php',
        namespace: 'workbench'
    );

    expect($viewPath->path)->toBe('/resources/views/welcome.blade.php')
        ->and($viewPath->namespace)->toBe('workbench');
});

it('can create a view path DTO without namespace', function () {
    $viewPath = new ViewPath(
        path: '/resources/views/welcome.blade.php'
    );

    expect($viewPath->path)->toBe('/resources/views/welcome.blade.php')
        ->and($viewPath->namespace)->toBeNull();
});

it('can create a view path DTO with nested path', function () {
    $viewPath = new ViewPath(
        path: '/resources/views/admin/users/index.blade.php',
        namespace: 'workbench'
    );

    expect($viewPath->path)->toBe('/resources/views/admin/users/index.blade.php')
        ->and($viewPath->namespace)->toBe('workbench');
});

it('can create a view path DTO with vendor namespace', function () {
    $viewPath = new ViewPath(
        path: '/vendor/views/package/view.blade.php',
        namespace: 'vendor-package'
    );

    expect($viewPath->namespace)->toBe('vendor-package');
});

it('handles absolute filesystem paths', function () {
    $viewPath = new ViewPath(
        path: '/absolute/path/to/views/home.blade.php',
        namespace: 'workbench'
    );

    expect($viewPath->path)->toBe('/absolute/path/to/views/home.blade.php');
});

it('handles relative paths', function () {
    $viewPath = new ViewPath(
        path: 'resources/views/page.blade.php',
        namespace: 'workbench'
    );

    expect($viewPath->path)->toBe('resources/views/page.blade.php');
});

it('handles paths with dots in filename', function () {
    $viewPath = new ViewPath(
        path: '/views/user.profile.blade.php',
        namespace: 'workbench'
    );

    expect($viewPath->path)->toBe('/views/user.profile.blade.php');
});

it('preserves path exactly as provided', function () {
    $path = '/very/long/path/to/the/view/file/name.blade.php';
    $viewPath = new ViewPath(
        path: $path,
        namespace: 'custom'
    );

    expect($viewPath->path)->toBe($path);
});

it('handles empty namespace', function () {
    $viewPath = new ViewPath(
        path: '/views/test.blade.php',
        namespace: null
    );

    expect($viewPath->namespace)->toBeNull();
});
