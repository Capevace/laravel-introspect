<?php

use Mateffy\Introspect\Baseline\ColumnSchema;
use Mateffy\Introspect\Baseline\IndexSchema;
use Mateffy\Introspect\Baseline\MigrationSchema;
use Mateffy\Introspect\Baseline\TableSchema;

it('creates ColumnSchema and converts to array', function () {
    $column = new ColumnSchema(
        type: 'string',
        nullable: false,
        default: null,
        autoincrement: false,
        unsigned: false,
    );

    $arr = $column->toArray();

    expect($arr)->toBe([
        'type' => 'string',
        'nullable' => false,
        'default' => null,
        'autoincrement' => false,
        'unsigned' => false,
    ]);
});

it('creates ColumnSchema with all values set', function () {
    $column = new ColumnSchema(
        type: 'integer',
        nullable: true,
        default: '0',
        autoincrement: true,
        unsigned: true,
    );

    $arr = $column->toArray();

    expect($arr['type'])->toBe('integer')
        ->and($arr['nullable'])->toBeTrue()
        ->and($arr['default'])->toBe('0')
        ->and($arr['autoincrement'])->toBeTrue()
        ->and($arr['unsigned'])->toBeTrue();
});

it('creates IndexSchema and converts to array', function () {
    $index = new IndexSchema(
        columns: ['id', 'name'],
        unique: true,
        primary: false,
    );

    $arr = $index->toArray();

    expect($arr)->toBe([
        'columns' => ['id', 'name'],
        'unique' => true,
        'primary' => false,
    ]);
});

it('creates IndexSchema with primary key', function () {
    $index = new IndexSchema(
        columns: ['id'],
        unique: true,
        primary: true,
    );

    $arr = $index->toArray();

    expect($arr['primary'])->toBeTrue()
        ->and($arr['unique'])->toBeTrue()
        ->and($arr['columns'])->toBe(['id']);
});

it('creates TableSchema and converts to array', function () {
    $columns = [
        'id' => new ColumnSchema('integer', false, null, true, true),
        'name' => new ColumnSchema('string', false, null, false, false),
    ];

    $indexes = [
        'users_pkey' => new IndexSchema(['id'], true, true),
        'users_name_unique' => new IndexSchema(['name'], true, false),
    ];

    $table = new TableSchema(columns: $columns, indexes: $indexes);

    $arr = $table->toArray();

    expect($arr)->toHaveKey('columns')
        ->and($arr)->toHaveKey('indexes')
        ->and($arr['columns'])->toHaveKey('id')
        ->and($arr['columns'])->toHaveKey('name')
        ->and($arr['columns']['id']['type'])->toBe('integer')
        ->and($arr['indexes'])->toHaveKey('users_pkey')
        ->and($arr['indexes']['users_pkey']['primary'])->toBeTrue();
});

it('creates TableSchema with empty indexes', function () {
    $columns = [
        'id' => new ColumnSchema('integer', false, null, true, false),
    ];

    $table = new TableSchema(columns: $columns, indexes: []);

    $arr = $table->toArray();

    expect($arr['columns'])->toHaveCount(1)
        ->and($arr['indexes'])->toBeEmpty();
});

it('creates MigrationSchema and converts to array', function () {
    $users = new TableSchema(
        columns: [
            'id' => new ColumnSchema('integer', false, null, true, false),
        ],
        indexes: [
            'users_pkey' => new IndexSchema(['id'], true, true),
        ],
    );

    $posts = new TableSchema(
        columns: [
            'id' => new ColumnSchema('integer', false, null, true, false),
            'title' => new ColumnSchema('string', false, null, false, false),
        ],
        indexes: [],
    );

    $migrationSchema = new MigrationSchema(tables: [
        'users' => $users,
        'posts' => $posts,
    ]);

    $arr = $migrationSchema->toArray();

    expect($arr)->toHaveKey('tables')
        ->and($arr['tables'])->toHaveKey('users')
        ->and($arr['tables'])->toHaveKey('posts')
        ->and($arr['tables']['users']['columns'])->toHaveKey('id')
        ->and($arr['tables']['posts']['columns'])->toHaveCount(2);
});

it('creates MigrationSchema with empty tables', function () {
    $migrationSchema = new MigrationSchema(tables: []);

    $arr = $migrationSchema->toArray();

    expect($arr['tables'])->toBeEmpty();
});
