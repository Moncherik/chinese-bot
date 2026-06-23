<?php

namespace App\Telegram\Handlers;

use App\Models\TelegramUser;
use App\Models\UserWord;
use App\Services\BotUserService;
use App\Services\CardService;
use App\Telegram\Menu;
use SergiX44\Nutgram\Nutgram;

class CommandHandler
{
    public function __construct(
        private readonly BotUserService $users,
        private readonly CardService $cards,
    ) {
    }

    /**
     * /start — greet, register the user, show the menu.
     */
    public function start(Nutgram $bot): void
    {
        $user = $this->users->resolveOrCreate($bot->user());

        $bot->sendMessage(
            text: $this->welcomeText($user),
            parse_mode: 'HTML',
            reply_markup: Menu::main(),
        );
    }

    /**
     * /help — list of available commands.
     */
    public function help(Nutgram $bot): void
    {
        $bot->sendMessage(text: $this->helpText(), parse_mode: 'HTML');
    }

    /**
     * /stats — progress overview.
     */
    public function stats(Nutgram $bot): void
    {
        $user = $this->users->resolveOrCreate($bot->user());

        $learned = UserWord::where('telegram_user_id', $user->id)
            ->whereNot('status', UserWord::STATUS_NEW)
            ->count();
        $mastered = UserWord::where('telegram_user_id', $user->id)
            ->where('status', UserWord::STATUS_MASTERED)
            ->count();
        $due = $this->cards->dueCount($user);
        $accuracy = $this->accuracy($user->id);

        $text = "📊 <b>Твой прогресс</b>\n\n"
              . "🔥 Серия дней: <b>{$user->streak_days}</b>\n"
              . "⭐ Опыт (XP): <b>{$user->xp}</b>\n\n"
              . "📚 В словаре изучается: <b>{$learned}</b> слов\n"
              . "🎓 Освоено: <b>{$mastered}</b>\n"
              . "⏰ На повторение сейчас: <b>{$due}</b>\n"
              . "🎯 Точность в тестах: <b>{$accuracy}%</b>";

        $bot->sendMessage(text: $text, parse_mode: 'HTML', reply_markup: Menu::main());
    }

    private function accuracy(int $userId): string
    {
        $stats = \App\Models\QuizResult::where('telegram_user_id', $userId)
            ->selectRaw('COUNT(*) AS total, SUM(is_correct::int) AS correct')
            ->first();

        if (!$stats || !$stats->total) {
            return '—';
        }

        return (string) round(($stats->correct / $stats->total) * 100);
    }

    private function welcomeText(TelegramUser $user): string
    {
        $name = $user->first_name ?: 'друг';

        return "你好, <b>{$name}</b>! 👋\n\n"
             . "Я помогу тебе учить китайские иероглифы и слова.\n\n"
             . "Выбирай, чем займёмся 👇\n\n"
             . "🃏 <b>Карточки</b> — учим новые слова и повторяем старые\n"
             . "📝 <b>Тест</b> — проверяем себя на 4 варианта\n"
             . "🔔 <b>Слово дня</b> — присылать новое слово каждый день\n"
             . "📊 <b>Статистика</b> — следим за прогрессом";
    }

    private function helpText(): string
    {
        return "📖 <b>Команды</b>\n\n"
             . "/start — главное меню\n"
             . "/cards — режим карточек (SRS)\n"
             . "/quiz — тест из 4 вариантов\n"
             . "/daily — подписка на слово дня\n"
             . "/stats — твоя статистика\n"
             . "/help — эта справка";
    }
}
