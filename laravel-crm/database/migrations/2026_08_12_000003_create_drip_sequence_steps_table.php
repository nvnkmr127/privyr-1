<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('day_offset')->default(0);
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the current hardcoded cadence so behaviour is unchanged post-migration.
        DB::table('drip_sequence_steps')->insert([
            ['name' => 'Day 0 Instant Auto-Responder', 'day_offset' => 0, 'content' => 'Hi {name}, thanks for reaching out regarding {title}! Here is our brochure: {brochure_link}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Day 2 Follow-up Check-in', 'day_offset' => 2, 'content' => 'Hi {name}, following up on your inquiry about {title}. Did you have any questions about our pricing or options?', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Day 5 Value Proposition & Case Study', 'day_offset' => 5, 'content' => 'Hi {name}, check out our latest project portfolio and client reviews for {title}: {brochure_link}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Day 14 Re-engagement Offer', 'day_offset' => 14, 'content' => "Hi {name}, we have an exclusive limited-time package deal for {title}. Reply YES if you'd like details!", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_sequence_steps');
    }
};
