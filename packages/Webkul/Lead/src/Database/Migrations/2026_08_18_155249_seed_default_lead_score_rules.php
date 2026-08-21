<?php

use Illuminate\Database\Migrations\Migration;
use Webkul\Lead\Models\LeadScoreRule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LeadScoreRule::create([
            'name' => 'Qualified',
            'type' => 'attribute',
            'conditions' => ['attribute' => 'qualification_status', 'operator' => '==', 'value' => 'qualified'],
            'points' => 25,
        ]);

        LeadScoreRule::create([
            'name' => 'High Value (Budget > 5L)',
            'type' => 'attribute',
            'conditions' => ['attribute' => 'lead_value', 'operator' => '>', 'value' => 500000],
            'points' => 20,
        ]);

        LeadScoreRule::create([
            'name' => 'Has Activity History',
            'type' => 'activity',
            'conditions' => [],
            'points' => 15,
        ]);

        LeadScoreRule::create([
            'name' => 'High Priority',
            'type' => 'attribute',
            'conditions' => ['attribute' => 'priority', 'operator' => 'in', 'value' => ['high', 'urgent']],
            'points' => 15,
        ]);

        LeadScoreRule::create([
            'name' => 'Has Contact Info',
            'type' => 'attribute',
            'conditions' => ['attribute' => 'name', 'operator' => 'not_null'],
            'points' => 10,
        ]);

        LeadScoreRule::create([
            'name' => 'Stale Lead (No activity 14+ days)',
            'type' => 'recency',
            'conditions' => ['days_since_activity' => 14],
            'points' => -20,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        LeadScoreRule::truncate();
    }
};
