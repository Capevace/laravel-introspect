<?php

use Mateffy\Introspect\Baseline\ColumnChange;
use Mateffy\Introspect\Baseline\ColumnSchema;
use Mateffy\Introspect\Baseline\ConfigDiff;
use Mateffy\Introspect\Baseline\DiffResult;
use Mateffy\Introspect\Baseline\FieldChange;
use Mateffy\Introspect\Baseline\MigrationDiff;
use Mateffy\Introspect\Baseline\RouteChange;
use Mateffy\Introspect\Baseline\RouteDiff;
use Mateffy\Introspect\Baseline\TableDiff;
use Mateffy\Introspect\Baseline\TableSchema;

it('creates FieldChange and converts to array', function () {
    $change = new FieldChange(from: 'string', to: 'integer');

    expect($change->from)->toBe('string')
        ->and($change->to)->toBe('integer')
        ->and($change->toArray())->toBe(['from' => 'string', 'to' => 'integer']);
});

it('creates FieldChange with array values', function () {
    $change = new FieldChange(from: ['GET'], to: ['GET', 'POST']);

    $arr = $change->toArray();

    expect($arr['from'])->toBe(['GET'])
        ->and($arr['to'])->toBe(['GET', 'POST']);
});

it('creates FieldChange with null values', function () {
    $change = new FieldChange(from: null, to: 'web');

    $arr = $change->toArray();

    expect($arr['from'])->toBeNull()
        ->and($arr['to'])->toBe('web');
});

it('creates ColumnChange and converts to array', function () {
    $change = new ColumnChange(changes: [
        'type' => new FieldChange(from: 'string', to: 'integer'),
        'nullable' => new FieldChange(from: true, to: false),
    ]);

    $arr = $change->toArray();

    expect($arr)->toHaveKey('type')
        ->and($arr)->toHaveKey('nullable')
        ->and($arr['type']['from'])->toBe('string')
        ->and($arr['type']['to'])->toBe('integer')
        ->and($arr['nullable']['from'])->toBeTrue()
        ->and($arr['nullable']['to'])->toBeFalse();
});

it('creates RouteChange and converts to array', function () {
    $change = new RouteChange(
        key: 'GET /users',
        uri: '/users',
        name: 'users.index',
        changes: [
            'middlewares' => new FieldChange(from: ['web'], to: ['web', 'auth']),
        ],
    );

    $arr = $change->toArray();

    expect($arr['key'])->toBe('GET /users')
        ->and($arr['uri'])->toBe('/users')
        ->and($arr['name'])->toBe('users.index')
        ->and($arr['changes']['middlewares']['from'])->toBe(['web'])
        ->and($arr['changes']['middlewares']['to'])->toBe(['web', 'auth']);
});

it('creates RouteDiff and converts to array', function () {
    $diff = new RouteDiff(
        added: [['uri' => '/new', 'name' => 'new.route']],
        removed: [['uri' => '/old', 'name' => 'old.route']],
        changed: [
            new RouteChange(
                key: 'GET /changed',
                uri: '/changed',
                name: 'changed.route',
                changes: ['name' => new FieldChange(from: 'changed.old', to: 'changed.route')],
            ),
        ],
    );

    $arr = $diff->toArray();

    expect($arr)->toHaveKey('added')
        ->and($arr)->toHaveKey('removed')
        ->and($arr)->toHaveKey('changed')
        ->and($arr['added'])->toHaveCount(1)
        ->and($arr['added'][0]['name'])->toBe('new.route')
        ->and($arr['removed'][0]['name'])->toBe('old.route')
        ->and($arr['changed'])->toHaveCount(1)
        ->and($arr['changed'][0]['key'])->toBe('GET /changed');
});

it('creates RouteDiff with empty arrays', function () {
    $diff = new RouteDiff(added: [], removed: [], changed: []);

    $arr = $diff->toArray();

    expect($arr['added'])->toBeEmpty()
        ->and($arr['removed'])->toBeEmpty()
        ->and($arr['changed'])->toBeEmpty();
});

it('creates TableDiff and converts to array', function () {
    $diff = new TableDiff(
        addedColumns: ['status' => ['type' => 'string', 'nullable' => true]],
        removedColumns: ['deprecated_field' => ['type' => 'text', 'nullable' => true]],
        changedColumns: [
            'name' => new ColumnChange([
                'type' => new FieldChange(from: 'text', to: 'string'),
            ]),
        ],
        addedIndexes: ['users_email_idx' => ['columns' => ['email'], 'unique' => false, 'primary' => false]],
        removedIndexes: ['users_old_idx' => ['columns' => ['old_col'], 'unique' => false, 'primary' => false]],
        changedIndexes: [
            'users_name_unique' => new FieldChange(
                from: ['columns' => ['name'], 'unique' => true, 'primary' => false],
                to: ['columns' => ['name', 'slug'], 'unique' => true, 'primary' => false],
            ),
        ],
    );

    $arr = $diff->toArray();

    expect($arr)->toHaveKey('added_columns')
        ->and($arr)->toHaveKey('removed_columns')
        ->and($arr)->toHaveKey('changed_columns')
        ->and($arr)->toHaveKey('added_indexes')
        ->and($arr)->toHaveKey('removed_indexes')
        ->and($arr)->toHaveKey('changed_indexes')
        ->and($arr['added_columns'])->toHaveKey('status')
        ->and($arr['removed_columns'])->toHaveKey('deprecated_field')
        ->and($arr['changed_columns']['name']['type']['from'])->toBe('text')
        ->and($arr['changed_columns']['name']['type']['to'])->toBe('string')
        ->and($arr['added_indexes'])->toHaveKey('users_email_idx')
        ->and($arr['removed_indexes'])->toHaveKey('users_old_idx')
        ->and($arr['changed_indexes']['users_name_unique']['from']['columns'])->toBe(['name'])
        ->and($arr['changed_indexes']['users_name_unique']['to']['columns'])->toBe(['name', 'slug']);
});

it('creates TableDiff with empty changes', function () {
    $diff = new TableDiff(
        addedColumns: [],
        removedColumns: [],
        changedColumns: [],
        addedIndexes: [],
        removedIndexes: [],
        changedIndexes: [],
    );

    $arr = $diff->toArray();

    expect($arr['added_columns'])->toBeEmpty()
        ->and($arr['removed_columns'])->toBeEmpty()
        ->and($arr['changed_columns'])->toBeEmpty()
        ->and($arr['added_indexes'])->toBeEmpty()
        ->and($arr['removed_indexes'])->toBeEmpty()
        ->and($arr['changed_indexes'])->toBeEmpty();
});

it('creates MigrationDiff and converts to array', function () {
    $addedColumn = new ColumnSchema('integer', false, null, true, false);
    $addedTable = new TableSchema(
        columns: ['id' => $addedColumn],
        indexes: [],
    );

    $changedTable = new TableDiff(
        addedColumns: ['email' => ['type' => 'string', 'nullable' => false]],
        removedColumns: [],
        changedColumns: [],
        addedIndexes: [],
        removedIndexes: [],
        changedIndexes: [],
    );

    $diff = new MigrationDiff(
        addedTables: ['notifications' => $addedTable->toArray()],
        removedTables: ['legacy_table' => ['columns' => [], 'indexes' => []]],
        changedTables: ['users' => $changedTable],
    );

    $arr = $diff->toArray();

    expect($arr)->toHaveKey('added_tables')
        ->and($arr)->toHaveKey('removed_tables')
        ->and($arr)->toHaveKey('changed_tables')
        ->and($arr['added_tables'])->toHaveKey('notifications')
        ->and($arr['removed_tables'])->toHaveKey('legacy_table')
        ->and($arr['changed_tables']['users']['added_columns'])->toHaveKey('email');
});

it('creates ConfigDiff and converts to array', function () {
    $diff = new ConfigDiff(
        added: ['app.new_key' => 'new_value'],
        removed: ['app.old_key' => 'old_value'],
        changed: ['app.name' => new FieldChange(from: 'OldApp', to: 'NewApp')],
    );

    $arr = $diff->toArray();

    expect($arr['added']['app.new_key'])->toBe('new_value')
        ->and($arr['removed']['app.old_key'])->toBe('old_value')
        ->and($arr['changed']['app.name']['from'])->toBe('OldApp')
        ->and($arr['changed']['app.name']['to'])->toBe('NewApp');
});

it('creates DiffResult and converts to array', function () {
    $routes = new RouteDiff(added: [], removed: [], changed: []);
    $migrations = new MigrationDiff(addedTables: [], removedTables: [], changedTables: []);
    $config = new ConfigDiff(added: [], removed: [], changed: []);

    $diff = new DiffResult(
        routes: $routes,
        migrations: $migrations,
        config: $config,
    );

    $arr = $diff->toArray();

    expect($arr)->toHaveKey('routes')
        ->and($arr)->toHaveKey('migrations')
        ->and($arr)->toHaveKey('config');
});

it('DiffResult hasChanges returns false when no changes', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [], changed: []),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: []),
    );

    expect($diff->hasChanges())->toBeFalse();
});

it('DiffResult hasChanges returns true when routes added', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [['uri' => '/new']], removed: [], changed: []),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: []),
    );

    expect($diff->hasChanges())->toBeTrue();
});

it('DiffResult hasChanges returns true when routes removed', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [['uri' => '/old']], changed: []),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: []),
    );

    expect($diff->hasChanges())->toBeTrue();
});

it('DiffResult hasChanges returns true when routes changed', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [], changed: [
            new RouteChange(key: 'GET /x', uri: '/x', name: 'x', changes: []),
        ]),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: []),
    );

    expect($diff->hasChanges())->toBeTrue();
});

it('DiffResult hasChanges returns true when tables added', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [], changed: []),
        migrations: new MigrationDiff(addedTables: ['new_table' => []], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: []),
    );

    expect($diff->hasChanges())->toBeTrue();
});

it('DiffResult hasChanges returns true when config changed', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [], changed: []),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: [], removed: [], changed: ['app.name' => new FieldChange('old', 'new')]),
    );

    expect($diff->hasChanges())->toBeTrue();
});

it('DiffResult serializes to JSON', function () {
    $diff = new DiffResult(
        routes: new RouteDiff(added: [], removed: [], changed: []),
        migrations: new MigrationDiff(addedTables: [], removedTables: [], changedTables: []),
        config: new ConfigDiff(added: ['app.new' => 'val'], removed: [], changed: []),
    );

    $json = $diff->toJson();
    $decoded = json_decode($json, true);

    expect($decoded)->not->toBeNull()
        ->and($decoded)->toHaveKey('routes')
        ->and($decoded)->toHaveKey('migrations')
        ->and($decoded)->toHaveKey('config')
        ->and($decoded['config']['added']['app.new'])->toBe('val');
});
