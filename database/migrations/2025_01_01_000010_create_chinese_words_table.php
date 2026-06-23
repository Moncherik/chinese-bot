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
        Schema::create('chinese_words', function (Blueprint $table) {
            $table->id();
            $table->string('hieroglyph');
            $table->string('pinyin');
            $table->string('meaning');
            $table->unsignedSmallInteger('hsk_level')->default(1);
            $table->timestamps();

            $table->index('hsk_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chinese_words');
    }
};
