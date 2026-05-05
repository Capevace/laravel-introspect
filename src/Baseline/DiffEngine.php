<?php

namespace Mateffy\Introspect\Baseline;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Route;

class DiffEngine
{
    public function diff(Baseline $old, Baseline $new): DiffResult
    {
        return new DiffResult(
            routes: $this->diffRoutes($old->routes, $new->routes),
            migrations: $this->diffMigrations($old->migrations, $new->migrations),
            config: $this->diffConfig($old->config, $new->config),
        );
    }

    protected function diffRoutes(Collection $oldRoutes, Collection $newRoutes): RouteDiff
    {
        $oldByKey = $oldRoutes->keyBy(fn ($r) => $this->routeKey($r));
        $newByKey = $newRoutes->keyBy(fn ($r) => $this->routeKey($r));

        $added = $newByKey->filter(fn ($r, $key) => ! $oldByKey->has($key))->values();
        $removed = $oldByKey->filter(fn ($r, $key) => ! $newByKey->has($key))->values();

        $changed = [];
        foreach ($newByKey as $key => $newRoute) {
            if ($oldByKey->has($key)) {
                $oldRoute = $oldByKey->get($key);
                $changes = $this->detectRouteChanges($oldRoute, $newRoute);
                if (! empty($changes)) {
                    $changed[] = new RouteChange(
                        key: $key,
                        uri: $newRoute->uri,
                        name: $newRoute->name,
                        changes: $changes,
                    );
                }
            }
        }

        return new RouteDiff(
            added: $added->map(fn ($r) => $r->toArray())->values()->all(),
            removed: $removed->map(fn ($r) => $r->toArray())->values()->all(),
            changed: $changed,
        );
    }

    protected function routeKey(Route $route): string
    {
        $methods = $route->methods;
        sort($methods);

        return implode(',', $methods).' '.$route->uri;
    }

    protected function detectRouteChanges(Route $old, Route $new): array
    {
        $changes = [];

        $fields = ['uri', 'name', 'methods', 'controller', 'action', 'middlewares'];

        foreach ($fields as $field) {
            $oldVal = $old->$field;
            $newVal = $new->$field;

            if (is_array($oldVal) || is_array($newVal)) {
                $oldVal = (array) $oldVal;
                $newVal = (array) $newVal;
                sort($oldVal);
                sort($newVal);
                if ($oldVal !== $newVal) {
                    $changes[$field] = new FieldChange(
                        from: $old->$field,
                        to: $new->$field,
                    );
                }
            } else {
                if ($oldVal !== $newVal) {
                    $changes[$field] = new FieldChange(
                        from: $oldVal,
                        to: $newVal,
                    );
                }
            }
        }

        return $changes;
    }

    protected function diffMigrations(MigrationSchema $old, MigrationSchema $new): MigrationDiff
    {
        $addedTables = [];
        $removedTables = [];
        $changedTables = [];

        $oldTableNames = array_keys($old->tables);
        $newTableNames = array_keys($new->tables);

        foreach (array_diff($newTableNames, $oldTableNames) as $table) {
            $addedTables[$table] = $new->tables[$table]->toArray();
        }

        foreach (array_diff($oldTableNames, $newTableNames) as $table) {
            $removedTables[$table] = $old->tables[$table]->toArray();
        }

        foreach (array_intersect($oldTableNames, $newTableNames) as $table) {
            $tableDiff = $this->diffTable($old->tables[$table], $new->tables[$table]);
            if ($tableDiff !== null) {
                $changedTables[$table] = $tableDiff;
            }
        }

        return new MigrationDiff(
            addedTables: $addedTables,
            removedTables: $removedTables,
            changedTables: $changedTables,
        );
    }

    protected function diffTable(TableSchema $old, TableSchema $new): ?TableDiff
    {
        $addedColumns = [];
        $removedColumns = [];
        $changedColumns = [];
        $addedIndexes = [];
        $removedIndexes = [];
        $changedIndexes = [];

        $oldColNames = array_keys($old->columns);
        $newColNames = array_keys($new->columns);

        foreach (array_diff($newColNames, $oldColNames) as $col) {
            $addedColumns[$col] = $new->columns[$col]->toArray();
        }

        foreach (array_diff($oldColNames, $newColNames) as $col) {
            $removedColumns[$col] = $old->columns[$col]->toArray();
        }

        foreach (array_intersect($oldColNames, $newColNames) as $col) {
            $colDiff = $this->diffColumn($old->columns[$col], $new->columns[$col]);
            if ($colDiff !== null) {
                $changedColumns[$col] = $colDiff;
            }
        }

        $oldIdxNames = array_keys($old->indexes);
        $newIdxNames = array_keys($new->indexes);

        foreach (array_diff($newIdxNames, $oldIdxNames) as $idx) {
            $addedIndexes[$idx] = $new->indexes[$idx]->toArray();
        }

        foreach (array_diff($oldIdxNames, $newIdxNames) as $idx) {
            $removedIndexes[$idx] = $old->indexes[$idx]->toArray();
        }

        foreach (array_intersect($oldIdxNames, $newIdxNames) as $idx) {
            $idxDiff = $this->diffIndex($old->indexes[$idx], $new->indexes[$idx]);
            if ($idxDiff !== null) {
                $changedIndexes[$idx] = $idxDiff;
            }
        }

        if (empty($addedColumns) && empty($removedColumns) && empty($changedColumns)
            && empty($addedIndexes) && empty($removedIndexes) && empty($changedIndexes)) {
            return null;
        }

        return new TableDiff(
            addedColumns: $addedColumns,
            removedColumns: $removedColumns,
            changedColumns: $changedColumns,
            addedIndexes: $addedIndexes,
            removedIndexes: $removedIndexes,
            changedIndexes: $changedIndexes,
        );
    }

    protected function diffColumn(ColumnSchema $old, ColumnSchema $new): ?ColumnChange
    {
        $changes = [];

        $fields = ['type', 'nullable', 'default', 'autoincrement', 'unsigned'];

        foreach ($fields as $field) {
            if ($old->$field !== $new->$field) {
                $changes[$field] = new FieldChange(
                    from: $old->$field,
                    to: $new->$field,
                );
            }
        }

        if (empty($changes)) {
            return null;
        }

        return new ColumnChange(changes: $changes);
    }

    protected function diffIndex(IndexSchema $old, IndexSchema $new): ?FieldChange
    {
        $oldArr = $old->toArray();
        $newArr = $new->toArray();

        sort($oldArr['columns']);
        sort($newArr['columns']);

        if ($oldArr === $newArr) {
            return null;
        }

        return new FieldChange(
            from: $oldArr,
            to: $newArr,
        );
    }

    protected function diffConfig(array $oldConfig, array $newConfig): ConfigDiff
    {
        $added = [];
        $removed = [];
        $changed = [];

        $oldKeys = array_keys($oldConfig);
        $newKeys = array_keys($newConfig);

        foreach (array_diff($newKeys, $oldKeys) as $key) {
            $added[$key] = $newConfig[$key];
        }

        foreach (array_diff($oldKeys, $newKeys) as $key) {
            $removed[$key] = $oldConfig[$key];
        }

        foreach (array_intersect($oldKeys, $newKeys) as $key) {
            if ($oldConfig[$key] !== $newConfig[$key]) {
                $changed[$key] = new FieldChange(
                    from: $oldConfig[$key],
                    to: $newConfig[$key],
                );
            }
        }

        return new ConfigDiff(
            added: $added,
            removed: $removed,
            changed: $changed,
        );
    }
}
