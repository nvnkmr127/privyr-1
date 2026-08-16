<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moldable_field_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('moldable_workspaces')->cascadeOnDelete();
            $table->string('entity_type', 80)->default('leads');
            $table->string('name');
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['workspace_id', 'entity_type', 'slug']);
        });

        Schema::create('moldable_field_group_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('moldable_field_groups')->cascadeOnDelete();
            $table->unsignedBigInteger('attribute_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['group_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moldable_field_group_attributes');
        Schema::dropIfExists('moldable_field_groups');
    }
};
