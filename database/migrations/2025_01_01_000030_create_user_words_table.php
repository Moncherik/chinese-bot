<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spaced-repetition state per (user, word): SM-2-ish fields.
     */
    public function up(): void
    {
        Schema::create('user_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chinese_word_id')->constrained()->cascadeOnDelete();
            // SM-2 algorithm state
            $table->unsignedTinyInteger('repetitions')->default(0);   // n
            $table->decimal('easiness', 3, 2)->default(2.50);        // EF
            $table->unsignedInteger('interval_days')->default(0);    // I(n)
            $table->timestamp('due_at')->nullable();                 // next review time
            $table->unsignedTinyInteger('status')->default(0);       // 0=new, 1=learning, 2=review, 3=mastered
            $table->timestamps();

            $table->unique(['telegram_user_id', 'chinese_word_id']);
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_words');
    }
};
