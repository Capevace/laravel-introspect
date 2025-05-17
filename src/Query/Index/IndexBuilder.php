<?php

namespace Mateffy\Introspect\Query\Index;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class IndexBuilder
{
    public string $dbPath;

    public function __construct(
        public string $projectPath,
        ?string $dbPath = null,
    ) {
        $this->dbPath = $dbPath ?? $this->projectPath.'/storage/introspect.db';
    }

    public function ensureDbExists(): bool
    {
        if (file_exists($this->dbPath)) {
            return false;
        }

        File::ensureDirectoryExists(dirname($this->dbPath));
        File::put($this->dbPath, '');

        return true;
    }

    public function db(): SQLiteConnection
    {
        $this->ensureDbExists();

        // Dynamically create a SQLite connection, as we're a library and can't edit
        // the database config directly.

        $config = [
            'driver' => 'sqlite',
            'database' => $this->dbPath,
            'foreign_key_constraints' => true,
        ];

        DB::disconnect('introspect');
        DB::purge('introspect');

        /** @var SQLiteConnection $connection */
        $connection = DB::connectUsing(
            name: 'introspect',
            config: $config
        );

        return $connection;
    }

    public function reindex(): void
    {
        $db = $this->db()->table();
    }

    public function migrate(): void
    {
        $db = $this->db();

        $builder = new Builder($db);
        $migration = new IndexMigration($builder);

        if ($db->statement('SELECT name FROM sqlite_master WHERE type="table" AND name="migrations"')) {
            $migration->down();
        }

        $migration->up();
    }
}
