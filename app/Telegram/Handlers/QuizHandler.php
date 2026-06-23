<?php

namespace App\Telegram\Handlers;

use App\Models\QuizResult;
use App\Services\BotUserService;
use App\Services\QuizService;
use App\Telegram\Menu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class QuizHandler
{
    public function __construct(
        private readonly BotUserService $users,
        private readonly QuizService $quiz,
    ) {
    }

    /**
     * Ask a new multiple-choice question.
     */
    public function ask(Nutgram $bot): void
    {
        $user = $this->users->resolveOrCreate($bot->user());

        $q = $this->quiz->makeQuestion($user);

        if (!$q) {
            $bot->sendMessage(
                text: "Недостаточно слов в словаре для теста.",
                parse_mode: 'HTML',
                reply_markup: Menu::main(),
            );
            return;
        }

        // Persist the correct answer between the question and the answer check.
        $bot->setUserData('quiz.answer_id', $q['answer_id']);

        $keyboard = InlineKeyboardMarkup::make();
        foreach ($q['options'] as $index => $opt) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    $opt['meaning'],
                    callback_data: "quiz:answer:{$q['answer_id']}:{$opt['id']}",
                )
            );
        }

        $bot->sendMessage(
            text: "📝 <b>Что значит этот иероглиф?</b>\n\n"
                . "<b>{$q['hieroglyph']}</b>  <i>{$q['pinyin']}</i>\n"
                . "<i>(HSK {$q['hsk_level']})</i>",
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /**
     * Evaluate the chosen option.
     */
    public function answer(Nutgram $bot): void
    {
        [, , $answerId, $chosenId] = explode(':', $bot->callbackQuery()->data);
        $answerId = (int) $answerId;
        $chosenId = (int) $chosenId;

        $isCorrect = $chosenId === $answerId;

        $user = $this->users->resolveOrCreate($bot->user());

        QuizResult::create([
            'telegram_user_id' => $user->id,
            'chinese_word_id'  => $answerId,
            'mode'             => 'meaning_guess',
            'is_correct'       => $isCorrect,
            'score_awarded'    => $isCorrect ? 10 : 0,
            'answered_at'      => now(),
        ]);

        if ($isCorrect) {
            $this->users->addXp($user, 10);
            $bot->answerCallbackQuery(text: '✅ Верно! +10 XP');
        } else {
            $bot->answerCallbackQuery(text: '❌ Неверно');
        }

        // Show the next question.
        $bot->editMessageText(
            text: $this->resultLine($isCorrect),
            parse_mode: 'HTML',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('➡ Следующий вопрос', callback_data: 'quiz:next'))
                ->addRow(InlineKeyboardButton::make('🏠 В меню', callback_data: 'menu:help')),
        );
    }

    /**
     * Advance to the next quiz question.
     */
    public function next(Nutgram $bot): void
    {
        $chatId = $bot->chatId();
        $messageId = $bot->callbackQuery()?->message?->message_id;

        if ($chatId !== null && $messageId !== null) {
            try {
                $bot->deleteMessage(chat_id: $chatId, message_id: $messageId);
            } catch (\Throwable) {
                // ignore
            }
        }

        $this->ask($bot);
    }

    private function resultLine(bool $isCorrect): string
    {
        return $isCorrect
            ? "✅ <b>Правильно!</b>"
            : "❌ <b>Неверно.</b> Не сдавайся!";
    }
}
