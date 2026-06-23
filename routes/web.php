<?php

use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', function () {
    return view('welcome');
});

// Telegram webhook endpoint.
// The {token} segment must match TELEGRAM_BOT_WEBHOOK_SECRET in .env
// so that randoms can't spam the route.
Route::post('/tg/webhook/{token}', function (string $token, Nutgram $bot) {
    abort_unless(
        hash_equals(config('nutgram.webhook_secret', ''), $token),
        403,
    );

    $bot->run();
    return response('ok');
});
