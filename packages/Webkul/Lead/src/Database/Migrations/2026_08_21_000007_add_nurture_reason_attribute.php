<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $attributeId = DB::table('attributes')->insertGetId([
            'code' => 'nurture_reason',
            'name' => 'Nurture Reason',
            'type' => 'select',
            'entity_type' => 'leads',
            'is_required' => 0,
            'is_unique' => 0,
            'value_per_locale' => 0,
            'value_per_channel' => 0,
            'is_filterable' => 0,
            'is_configurable' => 0,
            'is_user_defined' => 0,
            'is_visible_on_front' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $options = [
            'Not ready',
            'Budget later',
            'Future requirement',
            'No response',
            'Follow up next month',
            'Follow up next quarter',
            'Long decision cycle',
            'Other',
        ];

        foreach ($options as $index => $option) {
            DB::table('attribute_options')->insert([
                'attribute_id' => $attributeId,
                'name' => $option,
                'sort_order' => $index + 1,
            ]);
        }
    }

    public function down(): void
    {
        $attribute = DB::table('attributes')->where('code', 'nurture_reason')->where('entity_type', 'leads')->first();
        if ($attribute) {
            DB::table('attribute_options')->where('attribute_id', $attribute->id)->delete();
            DB::table('attributes')->where('id', $attribute->id)->delete();
        }
    }
};
