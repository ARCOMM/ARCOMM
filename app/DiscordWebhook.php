<?php

namespace App;

use GuzzleHttp\Client;

class DiscordWebhook
{
    public static function notifyArchub(string $content)
    {
        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
        $response = $client->request('POST', config('services.discord.archub_webhook'), [
            'json' => ['content' => $content],
        ]);
    }

    public static function notifyStaff(string $content)
    {
        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
        $response = $client->request('POST', config('services.discord.staff_webhook'), [
            'json' => ['content' => $content],
        ]);
    }
}
