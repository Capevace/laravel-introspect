<?php

namespace Mateffy\Introspect\Query\Index;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class IndexMigration extends Migration
{
    public function __construct(protected Builder $schema)
    {
    }

    public function up(): void
    {
        $this->schema->create('migrations', function (Blueprint $table) {
            $table->id();
        });

        $this->schema->create('classes', function (Blueprint $table) {
            $table->string('classpath')->primary();
            $table->string('namespace')
                ->index()
                ->nullable();
            $table->string('name')->index();
            $table->string('extends')
                ->index()
                ->nullable();

            $table->jsonb('implements')
                ->index()
                ->default('[]');
        });

        $this->schema->create('class_properties', function (Blueprint $table) {
            $table->string('classpath')->index();
            $table->string('name')->index();
            $table->string('description')->nullable();
            $table->string('default')->nullable();
            $table->boolean('readable');
            $table->boolean('writable');
            $table->boolean('fillable');
            $table->boolean('hidden');
            $table->boolean('appended');
            $table->boolean('relation');
            $table->string('cast')->nullable();
            $table->jsonb('types')->default('[]');

            $table->foreign('classpath')
                ->references('classpath')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('filament_api_table');
        $this->schema->dropIfExists('migrations');
    }
}
