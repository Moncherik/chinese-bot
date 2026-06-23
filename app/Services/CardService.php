<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\Word;
use App\Models\UserWord;
use Illuminate\Support\Carbon;

class CardService
{
    public function __construct(private readonly SpacedRepetition $srs)
    {
    }

    /**
     * Pick the next card to study for a user:
     *  1) a due card whose review time has passed,
     *  2) otherwise a brand-new word they have never seen.
     * Returns null if there is nothing to study.
     */
    public function nextCard(TelegramUser $user): ?array
    {
        // 1. Due cards first.
        $due = UserWord::where('telegram_user_id', $user->id)
            ->whereIn('status', [UserWord::STATUS_LEARNING, UserWord::STATUS_REVIEW])
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->with('word')
            ->first();

        if ($due) {
            return ['userWord' => $due, 'word' => $due->word, 'isNew' => false];
        }

        // 2. A word never seen before.
        $seenIds = UserWord::where('telegram_user_id', $user->id)->pluck('chinese_word_id');
        $word = Word::whereNotIn('id', $seenIds)
            ->orderBy('hsk_level')
            ->inRandomOrder()
            ->first();

        if (!$word) {
            return null; // dictionary exhausted
        }

        $userWord = $this->srs->initCard(
            UserWord::create([
                'telegram_user_id' => $user->id,
                'chinese_word_id'  => $word->id,
            ])
        );

        return ['userWord' => $userWord, 'word' => $word, 'isNew' => true];
    }

    /**
     * Count of cards waiting for review.
     */
    public function dueCount(TelegramUser $user): int
    {
        return UserWord::where('telegram_user_id', $user->id)
            ->whereIn('status', [UserWord::STATUS_LEARNING, UserWord::STATUS_REVIEW])
            ->where('due_at', '<=', now())
            ->count();
    }
}
