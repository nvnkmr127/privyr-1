<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        DB::table('attributes')->insert([
            [
                'code'            => 'qualification_notes',
                'name'            => 'Qualification Notes',
                'type'            => 'textarea',
                'entity_type'     => 'leads',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => '17',
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '0',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'code'            => 'disqualification_reason',
                'name'            => 'Disqualification Reason',
                'type'            => 'select',
                'entity_type'     => 'leads',
                'lookup_type'     => null,
                'validation'      => null,
                'sort_order'      => '18',
                'is_required'     => '0',
                'is_unique'       => '0',
                'quick_add'       => '0',
                'is_user_defined' => '0',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]
        ]);

        // Insert options for disqualification_reason
        $attribute = DB::table('attributes')->where('code', 'disqualification_reason')->first();

        if ($attribute) {
            $options = [
                'No requirement',
                'No budget',
                'Wrong location',
                'Wrong service',
                'No response',
                'Duplicate',
                'Invalid lead',
                'Competitor',
                'Other'
            ];

            foreach ($options as $index => $option) {
                DB::table('attribute_options')->insert([
                    'name'         => $option,
                    'attribute_id' => $attribute->id,
                    'sort_order'   => $index + 1,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};
