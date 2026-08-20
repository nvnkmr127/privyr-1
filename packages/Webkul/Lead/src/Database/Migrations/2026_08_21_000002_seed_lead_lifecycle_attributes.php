<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        $attributes = [
            [
                'code' => 'status',
                'name' => 'Status',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => 1,
                'is_required' => 1,
                'is_unique' => 0,
                'quick_add' => 1,
                'is_user_defined' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'temperature',
                'name' => 'Temperature',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => 2,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 1,
                'is_user_defined' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'lost_reason',
                'name' => 'Lost Reason',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => 3,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'junk_reason',
                'name' => 'Junk Reason',
                'type' => 'select',
                'entity_type' => 'leads',
                'sort_order' => 4,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('attributes')->insert($attributes);

        // Add Options
        $statusAttribute = DB::table('attributes')->where('code', 'status')->first();
        if ($statusAttribute) {
            DB::table('attribute_options')->insert([
                ['name' => 'Open', 'attribute_id' => $statusAttribute->id, 'sort_order' => 1],
                ['name' => 'Working', 'attribute_id' => $statusAttribute->id, 'sort_order' => 2],
                ['name' => 'Nurturing', 'attribute_id' => $statusAttribute->id, 'sort_order' => 3],
                ['name' => 'Converted', 'attribute_id' => $statusAttribute->id, 'sort_order' => 4],
                ['name' => 'Lost', 'attribute_id' => $statusAttribute->id, 'sort_order' => 5],
                ['name' => 'Junk', 'attribute_id' => $statusAttribute->id, 'sort_order' => 6],
            ]);
        }

        $tempAttribute = DB::table('attributes')->where('code', 'temperature')->first();
        if ($tempAttribute) {
            DB::table('attribute_options')->insert([
                ['name' => 'Cold', 'attribute_id' => $tempAttribute->id, 'sort_order' => 1],
                ['name' => 'Warm', 'attribute_id' => $tempAttribute->id, 'sort_order' => 2],
                ['name' => 'Hot', 'attribute_id' => $tempAttribute->id, 'sort_order' => 3],
            ]);
        }

        $lostAttribute = DB::table('attributes')->where('code', 'lost_reason')->first();
        if ($lostAttribute) {
            DB::table('attribute_options')->insert([
                ['name' => 'No response', 'attribute_id' => $lostAttribute->id, 'sort_order' => 1],
                ['name' => 'Not interested', 'attribute_id' => $lostAttribute->id, 'sort_order' => 2],
                ['name' => 'Budget', 'attribute_id' => $lostAttribute->id, 'sort_order' => 3],
                ['name' => 'Competitor', 'attribute_id' => $lostAttribute->id, 'sort_order' => 4],
                ['name' => 'Wrong requirement', 'attribute_id' => $lostAttribute->id, 'sort_order' => 5],
                ['name' => 'Wrong location', 'attribute_id' => $lostAttribute->id, 'sort_order' => 6],
                ['name' => 'Timing', 'attribute_id' => $lostAttribute->id, 'sort_order' => 7],
                ['name' => 'Duplicate', 'attribute_id' => $lostAttribute->id, 'sort_order' => 8],
                ['name' => 'Other', 'attribute_id' => $lostAttribute->id, 'sort_order' => 9],
            ]);
        }

        $junkAttribute = DB::table('attributes')->where('code', 'junk_reason')->first();
        if ($junkAttribute) {
            DB::table('attribute_options')->insert([
                ['name' => 'Spam', 'attribute_id' => $junkAttribute->id, 'sort_order' => 1],
                ['name' => 'Fake Info', 'attribute_id' => $junkAttribute->id, 'sort_order' => 2],
                ['name' => 'Not a fit', 'attribute_id' => $junkAttribute->id, 'sort_order' => 3],
                ['name' => 'Other', 'attribute_id' => $junkAttribute->id, 'sort_order' => 4],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $codes = ['status', 'temperature', 'lost_reason', 'junk_reason'];

        $attributes = DB::table('attributes')->whereIn('code', $codes)->get();
        foreach ($attributes as $attribute) {
            DB::table('attribute_options')->where('attribute_id', $attribute->id)->delete();
        }

        DB::table('attributes')->whereIn('code', $codes)->delete();
    }
};
