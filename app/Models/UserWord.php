<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWord extends Model
{
    public const STATUS_NEW      = 0;
    public const STATUS_LEARNING = 1;
    public const STATUS_REVIEW   = 2;
    public const STATUS_MASTERED = 3;

    protected $fillable = [
        'telegram_user_id',
        'chinese_word_id',
        'repetitions',
        'easiness',
        'interval_days',
        'due_at',
        'status',
    ];

    protected $casts = [
        'repetitions'  => 'integer',
        'easiness'     => 'float',
        'interval_days'=> 'integer',
        'due_at'       => 'datetime',
        'status'       => 'integer',
    ];

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'chinese_word_id');
    }

    /**
     * Whether the card is due for review right now.
     */
    public function isDue(): bool
    {
        return $this->due_at === null || $this->due_at->isPast();
    }
}
