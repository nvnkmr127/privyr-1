<?php

return [
    /**
     * Define the attribute codes that are required before a Lead can be marked as 'Qualified'.
     * These should match the 'code' column in the attributes table.
     */
    'required_attributes' => [
        'budget',
        'decision_maker',
        'requirement',
        'timeline',
    ],

    /**
     * Define the reasons available when disqualifying a lead.
     */
    'disqualification_reasons' => [
        'no_requirement' => 'No requirement',
        'budget_mismatch' => 'Budget mismatch',
        'wrong_location' => 'Wrong location',
        'not_decision_maker' => 'Not decision maker',
        'duplicate' => 'Duplicate',
        'invalid_lead' => 'Invalid Lead',
        'not_interested' => 'Not interested',
        'other' => 'Other',
    ],
];
