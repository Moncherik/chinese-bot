<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\Word;

class QuizService
{
    /**
     * Build a multiple-choice question: show hieroglyph, pick the meaning.
     * Returns an array with the question word and 4 shuffled options.
     */
    public function makeQuestion(TelegramUser $user): ?array
    {
        // Prefer words the user has already started learning, fall back to any.
        $pool = Word::inRandomOrder()->limit(4)->get();

        if ($pool->count() < 4) {
            return null; // not enough words in the dictionary
        }

        $answer = $pool->random();
        $options = $pool->shuffle()->values();

        return [
            'answer_id' => $answer->id,
            'hieroglyph' => $answer->hieroglyph,
            'pinyin'     => $answer->pinyin,
            'meaning'    => $answer->meaning,
            'hsk_level'  => $answer->hsk_level,
            'options'    => $options->map(fn (Word $w) => [
                'id'      => $w->id,
                'meaning' => $w->meaning,
            ])->all(),
        ];
    }
}
