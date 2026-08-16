<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the defaults so the content library is non-empty out of the box.
        DB::table('message_templates')->insert([
            ['name' => '👋 Welcome & Greeting', 'category' => 'Initial Contact', 'content' => 'Hi {name}, thanks for reaching out regarding {title}. I am {agent_name} from our team. How can we assist you today?', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '📄 Share Product Brochure', 'category' => 'Collateral', 'content' => 'Hi {name}, here is our latest product catalogue and portfolio for {title}. Let us know if you have any questions!', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '🔄 Post-Call Follow-up', 'category' => 'Follow Up', 'content' => "Hi {name}, great speaking with you! Following up on our discussion regarding {title}. Please let me know when you'd like to schedule our next call.", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '💰 Special Offer & Pricing', 'category' => 'Closing', 'content' => 'Hi {name}, we have an exclusive package offer for {title} valued at ₹{value}. Let me know if you would like to reserve this today!', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
