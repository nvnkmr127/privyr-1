<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lead Health Thresholds
    |--------------------------------------------------------------------------
    |
    | Define the conditions for a Lead to be marked as "Needs Attention",
    | "Inactive", or "Overdue". These thresholds help trigger automations
    | and highlight stale leads in the UI.
    |
    */

    'inactivity' => [
        // Number of days without any meaningful activity before a Lead is considered "Inactive"
        'inactive_days' => 14,
        
        // Number of days without activity before a Lead "Needs Attention"
        'needs_attention_days' => 7,
    ],

    'stage_aging' => [
        // Number of days a Lead can stay in one stage before triggering "Stage Stuck" alert
        'stuck_days' => 14,
    ],

    'follow_up' => [
        // Grace period in hours before a missed follow-up becomes "Overdue"
        'grace_period_hours' => 24,
    ],
    
    'batch_size' => 100, // For the evaluation command

];
