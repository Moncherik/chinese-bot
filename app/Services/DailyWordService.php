<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\Word;
use App\Models\UserWord;

class DailyWordService
{
    /**
     * Pick a word-of-the-day for a given user:
     *  a word they haven't started yet, lowest HSK level first.
     * Falls back to a random mastered word for reinforcement.
     */
    public function wordForUser(TelegramUser $user): ?Word
    {
        $seenIds = UserWord::where('telegram_user_id', $user->id)->pluck('chinese_word_id');

        $word = Word::whereNotIn('id', $seenIds)
            ->orderBy('hsk_level')
            ->inRandomOrder()
            ->first();

        if ($word) {
            return $word;
        }

        // Everything seen — reinforce a random one.
        return Word::inRandomOrder()->first();
    }

    /**
     * Format the daily-word message for a user.
     */
    public function messageForUser(TelegramUser $user): ?string
    {
        $word = $this->wordForUser($user);

        if (!$word) {
            return null;
        }

        return "🔔 <b>Слово дня</b>\n\n"
             . "<b>{$word->hieroglyph}</b>  <i>{$word->pinyin}</i>\n"
             . "📖 {$word->meaning}\n"
             . "<i>HSK {$word->hsk_level}</i>";
    }
}
