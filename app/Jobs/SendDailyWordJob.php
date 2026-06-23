<?php

namespace App\Jobs;

use App\Models\TelegramUser;
use App\Services\DailyWordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class SendDailyWordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $telegramUserId)
    {
    }

    public function handle(DailyWordService $daily, Nutgram $bot): void
    {
        /** @var TelegramUser|null $user */
        $user = TelegramUser::find($this->telegramUserId);

        if (!$user || !$user->daily_subscribed) {
            return;
        }

        $message = $daily->messageForUser($user);

        if (!$message) {
            return;
        }

        try {
            $bot->sendMessage(
                chat_id: $user->telegram_id,
                text: $message,
                parse_mode: 'HTML',
            );
        } catch (\Throwable $e) {
            Log::warning("Daily word send failed for user {$user->telegram_id}: {$e->getMessage()}");
        }
    }
}
