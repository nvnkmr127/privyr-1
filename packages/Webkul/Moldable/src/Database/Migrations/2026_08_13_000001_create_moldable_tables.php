<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moldable_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('moldable_workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('moldable_workspaces')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('role')->default('member');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['workspace_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('moldable_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('moldable_workspaces')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['workspace_id', 'slug']);
        });

        Schema::table('moldable_workspace_members', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('moldable_teams')->nullOnDelete();
        });

        Schema::create('moldable_saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('moldable_workspaces')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('entity_type', 80);
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->json('sort')->nullable();
            $table->json('group_by')->nullable();
            $table->string('visibility')->default('private');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['workspace_id', 'entity_type', 'visibility']);
        });

        Schema::create('moldable_industry_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('industry');
            $table->text('description')->nullable();
            $table->json('definition');
            $table->boolean('is_system')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('moldable_workspace_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('moldable_workspaces')->cascadeOnDelete();
            $table->string('resource_type', 80);
            $table->unsignedBigInteger('resource_id');
            $table->timestamps();
            $table->unique(['workspace_id', 'resource_type', 'resource_id']);
            $table->index(['resource_type', 'resource_id']);
        });

        $now = now();
        DB::table('moldable_industry_templates')->insert([
            [
                'key' => 'interior-design',
                'name' => 'Interior Design CRM',
                'industry' => 'Interior Design',
                'description' => 'Lead attributes, views, and follow-up defaults for interior design sales teams.',
                'definition' => json_encode([
                    'fields' => [
                        ['key' => 'property_type', 'label' => 'Property Type', 'type' => 'select', 'options' => ['Apartment', 'Villa', 'Office', 'Commercial']],
                        ['key' => 'budget', 'label' => 'Budget', 'type' => 'price'],
                        ['key' => 'project_location', 'label' => 'Project Location', 'type' => 'text'],
                        ['key' => 'possession_date', 'label' => 'Possession Date', 'type' => 'date'],
                    ],
                    'views' => [
                        ['name' => 'Hot Leads', 'filters' => [['field' => 'priority', 'operator' => 'in', 'value' => ['high', 'urgent']]]],
                        ['name' => 'Follow-up Due', 'filters' => [['field' => 'next_follow_up_at', 'operator' => 'not_empty']]],
                    ],
                ]),
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'real-estate',
                'name' => 'Real Estate CRM',
                'industry' => 'Real Estate',
                'description' => 'Lead attributes and views for property sales teams.',
                'definition' => json_encode([
                    'fields' => [
                        ['key' => 'property_type', 'label' => 'Property Type', 'type' => 'select', 'options' => ['Apartment', 'Villa', 'Plot', 'Commercial']],
                        ['key' => 'budget', 'label' => 'Budget', 'type' => 'price'],
                        ['key' => 'preferred_location', 'label' => 'Preferred Location', 'type' => 'text'],
                        ['key' => 'purchase_timeline', 'label' => 'Purchase Timeline', 'type' => 'select', 'options' => ['Immediate', '1-3 Months', '3-6 Months', '6+ Months']],
                    ],
                    'views' => [
                        ['name' => 'High Budget', 'filters' => [['field' => 'budget', 'operator' => 'gte', 'value' => 10000000]]],
                    ],
                ]),
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('moldable_workspace_resources');
        Schema::dropIfExists('moldable_industry_templates');
        Schema::dropIfExists('moldable_saved_views');
        Schema::dropIfExists('moldable_workspace_members');
        Schema::dropIfExists('moldable_teams');
        Schema::dropIfExists('moldable_workspaces');
    }
};
