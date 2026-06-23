<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'language_code',
        'xp',
        'streak_days',
        'last_activity_date',
        'daily_subscribed',
    ];

    protected $casts = [
        'telegram_id'        => 'integer',
        'xp'                 => 'integer',
        'streak_days'        => 'integer',
        'last_activity_date' => 'date',
        'daily_subscribed'   => 'boolean',
    ];

    public function userWords(): HasMany
    {
        return $this->hasMany(UserWord::class, 'telegram_user_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'telegram_user_id');
    }
}
