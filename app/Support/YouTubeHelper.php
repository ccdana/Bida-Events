<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouTubeHelper
{
    public static function extractVideoId(string $text): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?.*v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
            '/music\.youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function isYouTubeUrl(string $text): bool
    {
        return self::extractVideoId($text) !== null;
    }

    public static function normalizeUrl(string $text): ?string
    {
        $id = self::extractVideoId($text);

        return $id ? "https://www.youtube.com/watch?v={$id}" : null;
    }

    public static function fetchTitle(string $text): ?string
    {
        $url = self::normalizeUrl($text);
        if (! $url) {
            return null;
        }

        $id = self::extractVideoId($url);

        return Cache::remember("youtube_title_{$id}", 86400, function () use ($url) {
            try {
                $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                    'url' => $url,
                    'format' => 'json',
                ]);

                if ($response->successful()) {
                    return $response->json('title');
                }
            } catch (\Throwable) {
                // fallback silencioso
            }

            return null;
        });
    }

    /**
     * @return array{text: string, youtube_id: string|null, is_youtube: bool, url: string|null}
     */
    public static function formatSongEntry(string $contentText): array
    {
        $videoId = self::extractVideoId($contentText);

        if ($videoId) {
            $title = self::fetchTitle($contentText);

            return [
                'text' => $title ?? 'Video de YouTube',
                'youtube_id' => $videoId,
                'is_youtube' => true,
                'url' => self::normalizeUrl($contentText),
            ];
        }

        return [
            'text' => $contentText,
            'youtube_id' => null,
            'is_youtube' => false,
            'url' => null,
        ];
    }

    /**
     * @return array{id: int, text: string, youtube_id: string|null, is_youtube: bool, url: string|null, guest: string|null, at: string|null}
     */
    public static function formatContribution(\App\Models\GuestContribution $contribution): array
    {
        $formatted = self::formatSongEntry($contribution->content_text ?? '');

        return [
            'id' => $contribution->id,
            'text' => $formatted['text'],
            'youtube_id' => $formatted['youtube_id'],
            'is_youtube' => $formatted['is_youtube'],
            'url' => $formatted['url'],
            'guest' => $contribution->guest?->name,
            'at' => $contribution->created_at?->diffForHumans(),
        ];
    }
}
