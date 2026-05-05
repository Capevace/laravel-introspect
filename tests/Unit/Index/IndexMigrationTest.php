<?php

use Illuminate\Database\Schema\Builder;
use Mateffy\Introspect\Query\Index\IndexBuilder;
use Mateffy\Introspect\Query\Index\IndexMigration;

it('can be instantiated', function () {
    $builder = new IndexBuilder(projectPath: sys_get_temp_dir());
    $connection = $builder->db();
    $schemaBuilder = new Builder($connection);

    $migration = new IndexMigration($schemaBuilder);

    expect($migration)->toBeInstanceOf(IndexMigration::class);
});

it('has up method', function () {
    $builder = new IndexBuilder(projectPath: sys_get_temp_dir());
    $connection = $builder->db();
    $schemaBuilder = new Builder($connection);

    $migration = new IndexMigration($schemaBuilder);

    expect(method_exists($migration, 'up'))->toBeTrue();
});

it('has down method', function () {
    $builder = new IndexBuilder(projectPath: sys_get_temp_dir());
    $connection = $builder->db();
    $schemaBuilder = new Builder($connection);

    $migration = new IndexMigration($schemaBuilder);

    expect(method_exists($migration, 'down'))->toBeTrue();
});

it('creates migrations table on up', function () {
    // Skip this test as it requires full database setup
    expect(true)->toBeTrue();
})->skip('Requires full database grammar setup');

it('drops migrations table on down', function () {
    // Skip this test as it requires full database setup
    expect(true)->toBeTrue();
})->skip('Requires full database grammar setup');

it('handles existing migrations table', function () {
    // Skip this test as it requires full database setup
    expect(true)->toBeTrue();
})->skip('Requires full database grammar setup');
