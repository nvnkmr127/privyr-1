<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Altering tinyint to string may fail depending on DB engine if there are constraints,
        // but we will do it via raw queries or string if doctrine/dbal supports it well.
        // It's safer to drop and recreate the column or rename it.
        // Let's drop `status` if we don't have constraints, or just change it.
        Schema::table('leads', function (Blueprint $table) {
            $table->string('temperature')->nullable()->default('Cold')->after('priority');
            $table->string('junk_reason')->nullable()->after('lost_reason');
            $table->datetime('converted_at')->nullable()->after('closed_at');
            $table->integer('converted_by')->unsigned()->nullable()->after('converted_at');
            $table->foreign('converted_by')->references('id')->on('users')->onDelete('set null');
        });

        // The easiest way to migrate tinyint to string is to rename the old column, create new one, and move data.
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('status', 'legacy_status');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('status')->nullable()->after('legacy_status');
        });

        // Migrate Data
        DB::table('leads')->orderBy('id')->chunk(100, function ($leads) {
            foreach ($leads as $lead) {
                $newStatus = 'Open';

                // Fetch the lead's pipeline stage if needed, but we can just map based on legacy_status
                // Krayin's legacy boolean status: 1 = Active, 0 = Inactive
                $stage = DB::table('lead_pipeline_stages')->where('id', $lead->lead_pipeline_stage_id)->first();
                $stageCode = $stage ? $stage->code : 'new';

                if ($stageCode === 'won') {
                    $newStatus = 'Converted';
                } elseif ($stageCode === 'lost') {
                    $newStatus = 'Lost';
                } else {
                    $newStatus = $lead->legacy_status ? 'Working' : 'Open';
                }

                DB::table('leads')->where('id', $lead->id)->update([
                    'status' => $newStatus,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_by']);
            $table->dropColumn(['temperature', 'junk_reason', 'converted_at', 'converted_by']);
            $table->dropColumn('status');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('legacy_status', 'status');
        });
    }
};
