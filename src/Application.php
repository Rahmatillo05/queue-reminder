<?php

namespace app;

use GuzzleHttp\Exception\GuzzleException;

class Application
{
    public Telegram $telegram;
    public DatabaseHelper $databaseHelper;

    public function __construct()
    {
        $this->telegram = new Telegram();
        $this->databaseHelper = new DatabaseHelper();
    }

    public function run()
    {
        $task = $this->databaseHelper->getFirstTask();
        $user = $this->databaseHelper->getActiveUser($task->id);
        $date = date('Y-m-d');
        $message = "Bugungi ($date) {$task->name} navbati {$user->name} da!\nHurmatli {$user->name}!!!\nIltimos vazifani chin dildan va o'z vaqtida bajaring!!!";
        $this->telegram->sendMessage($message, $user->telegram_id);
        $this->databaseHelper->nextUser($user->user_id);
    }
}