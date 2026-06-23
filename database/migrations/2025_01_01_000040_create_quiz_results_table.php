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
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chinese_word_id')->nullable()->constrained()->nullOnDelete();
            // mode: meaning_guess (see hieroglyph, guess meaning) | hieroglyph_guess (reverse)
            $table->string('mode', 32)->default('meaning_guess');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('score_awarded')->default(0);
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();

            $table->index(['telegram_user_id', 'answered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
