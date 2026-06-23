<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyWordJob;
use App\Models\TelegramUser;
use Illuminate\Console\Command;

class SendDailyWordsCommand extends Command
{
    protected $signature = 'chinese:daily {--dry : Don\'t actually dispatch jobs}';

    protected $description = 'Dispatch the daily-word job to every subscribed user';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');

        $users = TelegramUser::where('daily_subscribed', true)->get();

        if ($users->isEmpty()) {
            $this->info('No subscribed users yet.');
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            if ($dry) {
                $this->line("Would send to user #{$user->id} ({$user->telegram_id})");
                continue;
            }
            SendDailyWordJob::dispatch($user->id);
        }

        $this->info($dry
            ? "Dry run: {$users->count()} user(s) would receive the daily word."
            : "Dispatched daily-word jobs to {$users->count()} user(s).");

        return self::SUCCESS;
    }
}
