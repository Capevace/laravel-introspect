<?php

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\ViewRepository;

it('can create a view repository DTO', function () {
    $views = collect(['welcome', 'about']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'workbench'
    );

    expect($repository->namespace)->toBe('workbench')
        ->and($repository->views)->toHaveCount(2)
        ->and($repository->views)->toBeInstanceOf(Collection::class);
});

it('can create a view repository without namespace', function () {
    $views = collect(['home', 'contact']);

    $repository = new ViewRepository(
        views: $views,
        namespace: null
    );

    expect($repository->namespace)->toBeNull()
        ->and($repository->views)->toHaveCount(2);
});

it('can create an empty view repository', function () {
    $repository = new ViewRepository(
        views: collect(),
        namespace: 'empty'
    );

    expect($repository->views)->toBeEmpty()
        ->and($repository->views)->toBeInstanceOf(Collection::class);
});

it('can create a vendor view repository', function () {
    $views = collect(['view1', 'view2']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'vendor-package'
    );

    expect($repository->namespace)->toBe('vendor-package');
});

it('gets views as absolute string with namespace', function () {
    $views = collect(['admin.users.index', 'admin.users.create']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'workbench'
    );

    $absoluteViews = $repository->getViewsAsAbsoluteString();

    expect($absoluteViews)->toHaveCount(2)
        ->toContain('workbench::admin.users.index')
        ->toContain('workbench::admin.users.create');
});

it('gets views without modification when no namespace', function () {
    $views = collect(['home', 'about']);

    $repository = new ViewRepository(
        views: $views,
        namespace: null
    );

    $absoluteViews = $repository->getViewsAsAbsoluteString();

    expect($absoluteViews)->toHaveCount(2)
        ->toContain('home')
        ->toContain('about');
});

it('preserves nested view names in repository', function () {
    $views = collect([
        'admin.users.index',
        'admin.users.create',
        'admin.dashboard',
    ]);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'workbench'
    );

    expect($repository->views)->toHaveCount(3);
    expect($repository->getViewsAsAbsoluteString()->first())->toBe('workbench::admin.users.index');
});

it('handles single view in repository', function () {
    $views = collect(['single']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'app'
    );

    expect($repository->views)->toHaveCount(1);
    expect($repository->getViewsAsAbsoluteString()->first())->toBe('app::single');
});

it('handles views with dots in names', function () {
    $views = collect(['user.profile', 'user.settings']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'workbench'
    );

    $absoluteViews = $repository->getViewsAsAbsoluteString();

    expect($absoluteViews)->toContain('workbench::user.profile');
});

it('returns collection type from getViewsAsAbsoluteString', function () {
    $views = collect(['test']);

    $repository = new ViewRepository(
        views: $views,
        namespace: 'workbench'
    );

    $result = $repository->getViewsAsAbsoluteString();

    expect($result)->toBeInstanceOf(Collection::class);
});
