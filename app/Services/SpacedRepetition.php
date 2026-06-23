<?php

namespace App\Services;

use App\Models\UserWord;

/**
 * Lightweight SM-2 spaced-repetition implementation.
 *
 * Quality of recall (q) is graded 0..5:
 *   0-2 = forgotten, 3 = hard, 4 = good, 5 = easy.
 * On failure (q < 3) repetitions restart; on success the interval grows.
 */
class SpacedRepetition
{
    private const MIN_EASINESS = 1.30;
    private const INITIAL_EASINESS = 2.50;

    /**
     * Apply a recall grade to a UserWord and return the updated model.
     *
     * @param  int  $quality  0..5
     */
    public function review(UserWord $userWord, int $quality): UserWord
    {
        $quality = max(0, min(5, $quality));

        if ($quality < 3) {
            // Lapse: start over.
            $userWord->repetitions = 0;
            $userWord->interval_days = 0;
            $userWord->status = UserWord::STATUS_LEARNING;
        } else {
            $n = $userWord->repetitions + 1;
            $userWord->repetitions = $n;

            $userWord->interval_days = match (true) {
                $n === 1 => 1,
                $n === 2 => 3,
                default  => (int) round($userWord->interval_days * $userWord->easiness),
            };

            $userWord->status = $userWord->interval_days >= 21
                ? UserWord::STATUS_MASTERED
                : UserWord::STATUS_REVIEW;
        }

        // Update easiness factor (EF).
        $ef = $userWord->easiness
            + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $userWord->easiness = max(self::MIN_EASINESS, $ef);

        // Schedule the next review.
        $userWord->due_at = now()->addDays($userWord->interval_days);
        $userWord->save();

        return $userWord;
    }

    /**
     * Bootstrap a brand-new card for a user.
     */
    public function initCard(UserWord $userWord): UserWord
    {
        $userWord->repetitions = 0;
        $userWord->easiness = self::INITIAL_EASINESS;
        $userWord->interval_days = 0;
        $userWord->status = UserWord::STATUS_NEW;
        $userWord->due_at = now();
        $userWord->save();

        return $userWord;
    }
}
