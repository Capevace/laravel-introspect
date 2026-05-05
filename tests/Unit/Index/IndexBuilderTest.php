<?php

use Illuminate\Database\SQLiteConnection;
use Mateffy\Introspect\Query\Index\IndexBuilder;

it('creates database if not exists', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    $created = $builder->ensureDbExists();

    expect($created)->toBeTrue()
        ->and(file_exists($tempPath.'/test.db'))->toBeTrue();

    // Cleanup
    unlink($tempPath.'/test.db');
    rmdir($tempPath);
});

it('returns false when database already exists', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();
    mkdir($tempPath, 0755, true);
    touch($tempPath.'/test.db');

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    $created = $builder->ensureDbExists();

    expect($created)->toBeFalse();

    // Cleanup
    unlink($tempPath.'/test.db');
    rmdir($tempPath);
});

it('creates SQLite connection', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    $connection = $builder->db();

    expect($connection)->toBeInstanceOf(SQLiteConnection::class);

    // Cleanup
    unlink($tempPath.'/test.db');
    rmdir($tempPath);
});

it('uses custom db path when provided', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();
    $customDbPath = sys_get_temp_dir().'/custom-'.uniqid().'.db';

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $customDbPath
    );

    $builder->ensureDbExists();

    expect(file_exists($customDbPath))->toBeTrue();

    // Cleanup
    unlink($customDbPath);
});

it('uses default db path when not provided', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();
    mkdir($tempPath, 0755, true);

    $builder = new IndexBuilder(projectPath: $tempPath);

    expect($builder->dbPath)->toBe($tempPath.'/storage/introspect.db');

    // Cleanup
    rmdir($tempPath);
});

it('creates storage directory if needed', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/storage/introspect.db'
    );

    $builder->ensureDbExists();

    expect(is_dir($tempPath.'/storage'))->toBeTrue();

    // Cleanup
    unlink($tempPath.'/storage/introspect.db');
    rmdir($tempPath.'/storage');
    rmdir($tempPath);
});

it('disconnects and purges existing connections', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    // First connection
    $connection1 = $builder->db();

    // Second connection should also work (disconnects/purges first)
    $connection2 = $builder->db();

    expect($connection1)->toBeInstanceOf(SQLiteConnection::class)
        ->and($connection2)->toBeInstanceOf(SQLiteConnection::class);

    // Cleanup
    unlink($tempPath.'/test.db');
    rmdir($tempPath);
});

it('has reindex method', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    expect(method_exists($builder, 'reindex'))->toBeTrue();

    // Cleanup
    @unlink($tempPath.'/test.db');
    @rmdir($tempPath);
});

it('has migrate method', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-'.uniqid();

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $tempPath.'/test.db'
    );

    expect(method_exists($builder, 'migrate'))->toBeTrue();

    // Cleanup
    @unlink($tempPath.'/test.db');
    @rmdir($tempPath);
});

it('stores project path', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-path';

    $builder = new IndexBuilder(projectPath: $tempPath);

    expect($builder->projectPath)->toBe($tempPath);
});

it('stores db path', function () {
    $tempPath = sys_get_temp_dir().'/test-introspect-db';
    $dbPath = sys_get_temp_dir().'/custom.db';

    $builder = new IndexBuilder(
        projectPath: $tempPath,
        dbPath: $dbPath
    );

    expect($builder->dbPath)->toBe($dbPath);
});
