<?php

namespace App\Telegram;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

/**
 * Helper to build the bot's inline keyboards.
 */
class Menu
{
    public const CALLBACK_CARDS = 'menu:cards';
    public const CALLBACK_QUIZ  = 'menu:quiz';
    public const CALLBACK_STATS = 'menu:stats';
    public const CALLBACK_DAILY = 'menu:daily';
    public const CALLBACK_HELP  = 'menu:help';

    public static function main(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🃏 Карточки', callback_data: self::CALLBACK_CARDS),
                InlineKeyboardButton::make('📝 Тест', callback_data: self::CALLBACK_QUIZ),
            )
            ->addRow(
                InlineKeyboardButton::make('🔔 Слово дня', callback_data: self::CALLBACK_DAILY),
                InlineKeyboardButton::make('📊 Статистика', callback_data: self::CALLBACK_STATS),
            )
            ->addRow(
                InlineKeyboardButton::make('❓ Помощь', callback_data: self::CALLBACK_HELP),
            );
    }
}
