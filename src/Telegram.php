<?php

namespace app;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Telegram
{
    const GROUP_ID = '-1002010267678';
    const BASE_URL = 'https://api.telegram.org/bot7818763392:AAHLAn4JJilsZRLJEB_JtQlrJFYCvQAQRrI';

    public function sendMessage($message, $chat_id): bool
    {

        $url = $this->generateUrl();

        try {
            $client = new Client();
            $client->request('POST', $url, [
                'form_params' => [
                    'chat_id' => self::GROUP_ID,
                    'text' => $message,
                ]
            ]);
            $client = new Client();
            $client->request('POST', $url, [
                'form_params' => [
                    'chat_id' => $chat_id,
                    'text' => $message,
                ]
            ]);
            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    private function generateUrl(): string
    {
        return self::BASE_URL . '/sendMessage';
    }

}