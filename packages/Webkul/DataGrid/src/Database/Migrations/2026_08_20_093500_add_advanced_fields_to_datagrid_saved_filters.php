<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('datagrid_saved_filters', function (Blueprint $table) {
            $table->enum('visibility', ['private', 'team', 'shared'])->default('private')->after('name');
            $table->text('description')->nullable()->after('visibility');
            $table->string('sort_column')->nullable()->after('applied');
            $table->string('sort_direction')->nullable()->after('sort_column');
            $table->json('columns')->nullable()->after('sort_direction');
            $table->boolean('is_default')->default(false)->after('columns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datagrid_saved_filters', function (Blueprint $table) {
            $table->dropColumn([
                'visibility',
                'description',
                'sort_column',
                'sort_direction',
                'columns',
                'is_default'
            ]);
        });
    }
};
