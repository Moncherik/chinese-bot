<?php

namespace App\Telegram\Handlers;

use App\Services\BotUserService;
use App\Telegram\Menu;
use SergiX44\Nutgram\Nutgram;

class MenuHandler
{
    public function __construct(
        private readonly BotUserService $users,
        private readonly CardHandler $cards,
        private readonly QuizHandler $quiz,
        private readonly CommandHandler $commands,
    ) {
    }

    /**
     * Central dispatcher for the inline-menu callback buttons.
     */
    public function dispatch(Nutgram $bot): void
    {
        $action = $bot->callbackQuery()?->data;
        $bot->answerCallbackQuery(); // clear the loading spinner

        match ($action) {
            Menu::CALLBACK_CARDS => $this->cards->show($bot),
            Menu::CALLBACK_QUIZ  => $this->quiz->ask($bot),
            Menu::CALLBACK_STATS => $this->commands->stats($bot),
            Menu::CALLBACK_HELP  => $this->commands->help($bot),
            Menu::CALLBACK_DAILY => $this->toggleDaily($bot),
            default              => null,
        };
    }

    /**
     * Toggle the daily-word subscription.
     */
    public function toggleDaily(Nutgram $bot): void
    {
        $user = $this->users->resolveOrCreate($bot->user());
        $user->daily_subscribed = !$user->daily_subscribed;
        $user->save();

        $state = $user->daily_subscribed ? '✅ включена' : '⏸ выключена';

        $bot->sendMessage(
            text: "🔔 Рассылка «Слово дня»: <b>{$state}</b>.\n\n"
                . "Каждый день я буду присылать новое слово для изучения.",
            parse_mode: 'HTML',
            reply_markup: Menu::main(),
        );
    }
}
