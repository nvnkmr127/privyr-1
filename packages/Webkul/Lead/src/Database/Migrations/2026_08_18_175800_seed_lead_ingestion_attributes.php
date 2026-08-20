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
                'code'          => 'medium',
                'name'          => 'Medium',
                'type'          => 'text',
                'entity_type'   => 'leads',
                'lookup_type'   => null,
                'validation'    => null,
                'sort_order'    => 10,
                'is_required'   => 0,
                'is_unique'     => 0,
                'quick_add'     => 0,
                'is_user_defined' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'content',
                'name'          => 'Content',
                'type'          => 'text',
                'entity_type'   => 'leads',
                'lookup_type'   => null,
                'validation'    => null,
                'sort_order'    => 11,
                'is_required'   => 0,
                'is_unique'     => 0,
                'quick_add'     => 0,
                'is_user_defined' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'term',
                'name'          => 'Term',
                'type'          => 'text',
                'entity_type'   => 'leads',
                'lookup_type'   => null,
                'validation'    => null,
                'sort_order'    => 12,
                'is_required'   => 0,
                'is_unique'     => 0,
                'quick_add'     => 0,
                'is_user_defined' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'landing_page',
                'name'          => 'Landing Page',
                'type'          => 'text',
                'entity_type'   => 'leads',
                'lookup_type'   => null,
                'validation'    => 'url',
                'sort_order'    => 13,
                'is_required'   => 0,
                'is_unique'     => 0,
                'quick_add'     => 0,
                'is_user_defined' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'form_id',
                'name'          => 'Form ID',
                'type'          => 'text',
                'entity_type'   => 'leads',
                'lookup_type'   => null,
                'validation'    => null,
                'sort_order'    => 14,
                'is_required'   => 0,
                'is_unique'     => 0,
                'quick_add'     => 0,
                'is_user_defined' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        DB::table('attributes')->insert($attributes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('attributes')
            ->where('entity_type', 'leads')
            ->whereIn('code', ['medium', 'content', 'term', 'landing_page', 'form_id'])
            ->delete();
    }
};
