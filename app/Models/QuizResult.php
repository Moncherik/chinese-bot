<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResult extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'chinese_word_id',
        'mode',
        'is_correct',
        'score_awarded',
        'answered_at',
    ];

    protected $casts = [
        'is_correct'     => 'boolean',
        'score_awarded'  => 'integer',
        'answered_at'    => 'datetime',
    ];

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'chinese_word_id');
    }
}
