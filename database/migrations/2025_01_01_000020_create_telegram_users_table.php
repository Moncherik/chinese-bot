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
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('language_code', 10)->nullable();
            // gamification & streak tracking
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('streak_days')->default(0);
            $table->date('last_activity_date')->nullable();
            // SRS: scheduled daily-word notifications
            $table->boolean('daily_subscribed')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};
