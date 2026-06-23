<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    protected $table = 'chinese_words';

    protected $fillable = [
        'hieroglyph',
        'pinyin',
        'meaning',
        'hsk_level',
    ];

    protected $casts = [
        'hsk_level' => 'integer',
    ];

    public function userWords(): HasMany
    {
        return $this->hasMany(UserWord::class, 'chinese_word_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'chinese_word_id');
    }
}
