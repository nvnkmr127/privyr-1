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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('person_name')->nullable()->after('user_id');
            $table->json('emails')->nullable()->after('person_name');
            $table->json('contact_numbers')->nullable()->after('emails');
            $table->string('organization_name')->nullable()->after('contact_numbers');
        });

        // Copy data from persons to leads
        DB::table('leads')
            ->join('persons', 'leads.person_id', '=', 'persons.id')
            ->leftJoin('organizations', 'persons.organization_id', '=', 'organizations.id')
            ->update([
                'leads.person_name' => DB::raw('persons.name'),
                'leads.emails' => DB::raw('persons.emails'),
                'leads.contact_numbers' => DB::raw('persons.contact_numbers'),
                'leads.organization_name' => DB::raw('organizations.name'),
            ]);

        // Migrate emails that have person_id but no lead_id
        DB::statement('
            UPDATE emails 
            JOIN leads ON emails.person_id = leads.person_id 
            SET emails.lead_id = leads.id 
            WHERE emails.lead_id IS NULL AND emails.person_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['person_name', 'emails', 'contact_numbers', 'organization_name']);
        });
    }
};
