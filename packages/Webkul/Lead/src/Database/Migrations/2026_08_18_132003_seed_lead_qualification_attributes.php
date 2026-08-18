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
        $now = \Carbon\Carbon::now();

        $attributes = [
            [
                'code' => 'budget',
                'name' => 'Budget',
                'type' => 'price',
                'entity_type' => 'leads',
                'sort_order' => '10',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'requirement',
                'name' => 'Requirement',
                'type' => 'textarea',
                'entity_type' => 'leads',
                'sort_order' => '11',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'timeline',
                'name' => 'Timeline',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => '12',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'intent',
                'name' => 'Intent',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => '13',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'decision_maker',
                'name' => 'Decision Maker',
                'type' => 'boolean',
                'entity_type' => 'leads',
                'sort_order' => '14',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'qualification_checklist',
                'name' => 'Qualification Checklist',
                'type' => 'multiselect',
                'entity_type' => 'leads',
                'sort_order' => '15',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '0',
                'is_user_defined' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('attributes')->insert($attributes);

        // Options for Timeline
        $timelineAttr = DB::table('attributes')->where('code', 'timeline')->first();
        if ($timelineAttr) {
            DB::table('attribute_options')->insert([
                ['attribute_id' => $timelineAttr->id, 'name' => 'Immediate', 'sort_order' => 1],
                ['attribute_id' => $timelineAttr->id, 'name' => '1-3 months', 'sort_order' => 2],
                ['attribute_id' => $timelineAttr->id, 'name' => '3-6 months', 'sort_order' => 3],
                ['attribute_id' => $timelineAttr->id, 'name' => '6+ months', 'sort_order' => 4],
            ]);
        }

        // Options for Intent
        $intentAttr = DB::table('attributes')->where('code', 'intent')->first();
        if ($intentAttr) {
            DB::table('attribute_options')->insert([
                ['attribute_id' => $intentAttr->id, 'name' => 'High', 'sort_order' => 1],
                ['attribute_id' => $intentAttr->id, 'name' => 'Medium', 'sort_order' => 2],
                ['attribute_id' => $intentAttr->id, 'name' => 'Low', 'sort_order' => 3],
            ]);
        }

        // Options for Checklist
        $checklistAttr = DB::table('attributes')->where('code', 'qualification_checklist')->first();
        if ($checklistAttr) {
            DB::table('attribute_options')->insert([
                ['attribute_id' => $checklistAttr->id, 'name' => 'Budget Confirmed', 'sort_order' => 1],
                ['attribute_id' => $checklistAttr->id, 'name' => 'Authority Verified', 'sort_order' => 2],
                ['attribute_id' => $checklistAttr->id, 'name' => 'Need Identified', 'sort_order' => 3],
                ['attribute_id' => $checklistAttr->id, 'name' => 'Timeline Established', 'sort_order' => 4],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attributes')->whereIn('code', [
            'budget',
            'requirement',
            'timeline',
            'intent',
            'decision_maker',
            'qualification_checklist'
        ])->delete();
    }
};
