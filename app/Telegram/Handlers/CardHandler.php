<?php

namespace App\Telegram\Handlers;

use App\Models\UserWord;
use App\Services\BotUserService;
use App\Services\CardService;
use App\Services\SpacedRepetition;
use App\Telegram\Menu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class CardHandler
{
    public function __construct(
        private readonly BotUserService $users,
        private readonly CardService $cards,
        private readonly SpacedRepetition $srs,
    ) {
    }

    /**
     * Show the front side of the next card (just the hieroglyph),
     * with a button to reveal the back.
     */
    public function show(Nutgram $bot): void
    {
        $user = $this->users->resolveOrCreate($bot->user());

        $next = $this->cards->nextCard($user);

        if (!$next) {
            $bot->sendMessage(
                text: "🎉 Ты прошёл все доступные слова! Возвращайся позже — новые карточки появятся на повторении.",
                parse_mode: 'HTML',
                reply_markup: Menu::main(),
            );
            return;
        }

        $userWord = $next['userWord'];
        /** @var \App\Models\Word $word */
        $word = $next['word'];
        $tag = $next['isNew'] ? '🆕 Новое слово' : '🔁 Повторение';

        $text = "{$tag}\n\n"
              . "<b>{$word->hieroglyph}</b>\n"
              . "<i>HSK {$word->hsk_level}</i>\n\n"
              . "Нажми «Показать», чтобы увидеть перевод и пиньинь.";

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('👁 Показать', callback_data: "card:flip:{$userWord->id}")),
        );
    }

    /**
     * Flip the card: show meaning + pinyin and the self-grade buttons.
     */
    public function flip(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();
        // callback data is "card:flip:{id}"
        $userWordId = (int) (explode(':', $bot->callbackQuery()->data)[2] ?? 0);
        $userWord = UserWord::with('word')->find($userWordId);

        if (!$userWord) {
            $bot->sendMessage('Карточка устарела. Открой новую через /cards.');
            return;
        }

        $word = $userWord->word;

        $text = "🃏 <b>{$word->hieroglyph}</b> — {$word->pinyin}\n\n"
              . "📖 {$word->meaning}\n"
              . "<i>HSK {$word->hsk_level}</i>\n\n"
              . "Как хорошо ты вспомнил?";

        $bot->editMessageText(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('😵 Забыл', callback_data: "card:grade:{$userWord->id}:0"),
                    InlineKeyboardButton::make('😐 Трудно', callback_data: "card:grade:{$userWord->id}:3"),
                    InlineKeyboardButton::make('🙂 Вспомнил', callback_data: "card:grade:{$userWord->id}:4"),
                    InlineKeyboardButton::make('😎 Легко', callback_data: "card:grade:{$userWord->id}:5"),
                )
                ->addRow(InlineKeyboardButton::make('⏭ Дальше', callback_data: 'card:next')),
        );
    }

    /**
     * Apply the recall grade to the current card via the SRS algorithm.
     */
    public function grade(Nutgram $bot): void
    {
        [, , $userWordId, $quality] = explode(':', $bot->callbackQuery()->data);

        $userWord = UserWord::find((int) $userWordId);
        if (!$userWord) {
            $bot->answerCallbackQuery(text: 'Карточка не найдена.');
            return;
        }

        $this->srs->review($userWord, (int) $quality);

        // XP: small reward scaled by recall quality.
        $user = $this->users->resolveOrCreate($bot->user());
        $xp = match ((int) $quality) {
            0       => 1,
            3       => 3,
            4       => 5,
            default => 8,
        };
        $this->users->addXp($user, $xp);

        $verdict = match ((int) $quality) {
            0       => '😢 Забыл — повторим скоро.',
            3       => '😐 Трудно, но идём дальше.',
            4       => '🙂 Хорошо!',
            default => '😎 Отлично!',
        };

        $bot->answerCallbackQuery(text: "{$verdict} +{$xp} XP");
    }

    /**
     * Move on to the next card.
     */
    public function next(Nutgram $bot): void
    {
        // Delete the previous message to keep the chat tidy.
        $chatId = $bot->chatId();
        $messageId = $bot->callbackQuery()?->message?->message_id;

        if ($chatId !== null && $messageId !== null) {
            try {
                $bot->deleteMessage(chat_id: $chatId, message_id: $messageId);
            } catch (\Throwable) {
                // ignore — deleting an already-edited/old message is harmless
            }
        }

        $this->show($bot);
    }
}
