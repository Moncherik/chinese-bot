<?php

/** @var \SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Handlers\CardHandler;
use App\Telegram\Handlers\CommandHandler;
use App\Telegram\Handlers\MenuHandler;
use App\Telegram\Handlers\QuizHandler;
use App\Telegram\Menu;

/*
|--------------------------------------------------------------------------
| Bot commands
|--------------------------------------------------------------------------
*/

$bot->onCommand('start', [CommandHandler::class, 'start'])->description('Главное меню');
$bot->onCommand('help',  [CommandHandler::class, 'help'])->description('Справка');
$bot->onCommand('stats', [CommandHandler::class, 'stats'])->description('Моя статистика');
$bot->onCommand('cards', [CardHandler::class, 'show'])->description('Карточки (SRS)');
$bot->onCommand('quiz',  [QuizHandler::class, 'ask'])->description('Тест из 4 вариантов');
$bot->onCommand('daily', [MenuHandler::class, 'toggleDaily'])->description('Подписка на слово дня');

/*
|--------------------------------------------------------------------------
| Inline menu callbacks
|--------------------------------------------------------------------------
*/

// Main menu buttons: exact matches like "menu:cards".
$bot->onCallbackQueryData(Menu::CALLBACK_CARDS, [MenuHandler::class, 'dispatch']);
$bot->onCallbackQueryData(Menu::CALLBACK_QUIZ,  [MenuHandler::class, 'dispatch']);
$bot->onCallbackQueryData(Menu::CALLBACK_STATS, [MenuHandler::class, 'dispatch']);
$bot->onCallbackQueryData(Menu::CALLBACK_HELP,  [MenuHandler::class, 'dispatch']);
$bot->onCallbackQueryData(Menu::CALLBACK_DAILY, [MenuHandler::class, 'dispatch']);

/*
|--------------------------------------------------------------------------
| Card flow callbacks: patterns
|--------------------------------------------------------------------------
*/

// Reveal the back side: "card:flip:{id}"
$bot->onCallbackQueryData('#^card:flip:(\d+)$#', [CardHandler::class, 'flip']);
// Apply a recall grade: "card:grade:{id}:{quality}"
$bot->onCallbackQueryData('#^card:grade:(\d+):(\d+)$#', [CardHandler::class, 'grade']);
// Show the next card.
$bot->onCallbackQueryData('#^card:next$#', [CardHandler::class, 'next']);

/*
|--------------------------------------------------------------------------
| Quiz flow callbacks: patterns
|--------------------------------------------------------------------------
*/

// Pick an option: "quiz:answer:{answerId}:{chosenId}"
$bot->onCallbackQueryData('#^quiz:answer:(\d+):(\d+)$#', [QuizHandler::class, 'answer']);
// Next question.
$bot->onCallbackQueryData('#^quiz:next$#', [QuizHandler::class, 'next']);

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/

// Any callback we didn't handle explicitly routes through the menu dispatcher.
$bot->onCallbackQuery([MenuHandler::class, 'dispatch']);
