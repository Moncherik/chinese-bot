<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TextToSpeechService
{
    /**
     * Synthesize Mandarin speech for a text via Google Translate TTS.
     * Returns the local file path of the MP3, or null on failure.
     *
     * Note: Google's tts endpoint is unofficial but works for low volumes
     * and returns natural Mandarin pronunciation.
     */
    public function synthesizeMandarin(string $text): ?string
    {
        $fileName = 'tts/' . md5($text) . '.mp3';
        $disk = Storage::disk('local');

        if ($disk->exists($fileName)) {
            return $disk->path($fileName);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get('https://translate.google.com/translate_tts', [
                'ie'     => 'UTF-8',
                'client' => 'tw-ob',
                'tl'     => 'zh-CN',     // Mandarin Chinese
                'q'      => $text,
            ]);

            if ($response->successful() && strlen($response->body()) > 1000) {
                $disk->put($fileName, $response->body());
                return $disk->path($fileName);
            }
        } catch (\Throwable $e) {
            Log::warning("TTS failed for '{$text}': " . $e->getMessage());
        }

        return null;
    }
}
