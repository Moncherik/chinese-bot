<?php

namespace App\Services;

use App\Models\TelegramUser;
use Carbon\Carbon;
use SergiX44\Nutgram\Telegram\Properties\MessageType;
use SergiX44\Nutgram\Telegram\Types\User\User;

class BotUserService
{
    /**
     * Resolve or create a TelegramUser row from the incoming Telegram user.
     * Also keeps the day-streak up to date.
     */
    public function resolveOrCreate(User $from): TelegramUser
    {
        $user = TelegramUser::firstOrNew(['telegram_id' => $from->id]);

        $isNew = !$user->exists;

        $user->fill([
            'username'      => $from->username,
            'first_name'    => $from->first_name,
            'language_code' => $from->language_code,
        ]);

        if ($isNew) {
            $user->xp = 0;
            $user->streak_days = 0;
            $user->daily_subscribed = true;
            $user->save();
        } else {
            $user->save();
        }

        $this->touchStreak($user);

        return $user;
    }

    /**
     * Update the consecutive-days streak based on today's activity.
     */
    public function touchStreak(TelegramUser $user): void
    {
        $today = Carbon::today();

        if ($user->last_activity_date?->isSameDay($today)) {
            return; // already counted today
        }

        $yesterday = Carbon::yesterday();
        if ($user->last_activity_date && $user->last_activity_date->isSameDay($yesterday)) {
            $user->streak_days += 1;
        } else {
            $user->streak_days = 1;
        }

        $user->last_activity_date = $today;
        $user->save();
    }

    /**
     * Award experience points to a user.
     */
    public function addXp(TelegramUser $user, int $points): void
    {
        $user->increment('xp', $points);
    }
}
